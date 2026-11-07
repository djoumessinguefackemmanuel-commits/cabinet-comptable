<?php

namespace App\Services;

use App\Core\Database;

class TaxCalculationService
{
    public function calculateVAT(
        int $exerciseId,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        if ($exerciseId <= 0) {
            throw new \InvalidArgumentException('Un exercice comptable est requis.');
        }

        foreach ([$startDate, $endDate] as $date) {
            if ($date !== null && $date !== '' && !$this->isValidDate($date)) {
                throw new \InvalidArgumentException('La période sélectionnée est invalide.');
            }
        }
        if ($startDate && $endDate && $startDate > $endDate) {
            throw new \InvalidArgumentException('La date de début doit précéder la date de fin.');
        }

        $sql = "
            SELECT id, name, tax_type, taxable_amount, tax_rate, tax_amount,
                   declaration_date
            FROM taxes
            WHERE exercise_id = :exercise_id
              AND LOWER(tax_type) IN (
                  'collected_vat', 'vat_collected', 'tva_collectee',
                  'deductible_vat', 'vat_deductible', 'tva_deductible'
              )
        ";
        $params = ['exercise_id' => $exerciseId];

        if ($startDate) {
            $sql .= ' AND declaration_date >= :start_date';
            $params['start_date'] = $startDate;
        }
        if ($endDate) {
            $sql .= ' AND declaration_date <= :end_date';
            $params['end_date'] = $endDate;
        }
        $sql .= ' ORDER BY declaration_date ASC, id ASC';

        $statement = Database::connect()->prepare($sql);
        $statement->execute($params);
        $records = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $collectedVat = 0.0;
        $deductibleVat = 0.0;
        $operations = [];
        $deductibleTypes = ['deductible_vat', 'vat_deductible', 'tva_deductible'];
        foreach ($records as $record) {
            $type = strtolower($record['tax_type']);
            $amount = (float) $record['tax_amount'];
            if (in_array($type, $deductibleTypes, true)) {
                $deductibleVat += $amount;
            } else {
                $collectedVat += $amount;
            }

            $operations[] = [
                'date' => $record['declaration_date'],
                'reference' => 'TAX-' . $record['id'],
                'description' => $record['name'],
                'amount_ht' => (float) $record['taxable_amount'],
                'vat_rate' => (float) $record['tax_rate'],
                'vat_amount' => $amount
            ];
        }

        return [
            'collected_vat' => round($collectedVat, 2),
            'deductible_vat' => round($deductibleVat, 2),
            'operations' => $operations
        ];
    }

    /**
     * Calculer le montant d'une taxe.
     */
    public function calculateTax(
        float $baseAmount,
        float $taxRate
    ): float {

        if ($baseAmount < 0 || $taxRate < 0) {
            throw new \InvalidArgumentException(
                'La base imposable et le taux de taxe doivent être positifs.'
            );
        }

        $taxAmount = $baseAmount * ($taxRate / 100);

        return round($taxAmount, 2);
    }

    /**
     * Calculer le montant TTC.
     */
    public function calculateTotalWithTax(
        float $baseAmount,
        float $taxRate
    ): array {

        $taxAmount = $this->calculateTax(
            $baseAmount,
            $taxRate
        );

        $totalAmount = $baseAmount + $taxAmount;

        return [
            'base_amount' => round($baseAmount, 2),
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total_amount' => round($totalAmount, 2)
        ];
    }

    /**
     * Extraire le montant hors taxe à partir d'un montant TTC.
     */
    public function calculateAmountWithoutTax(
        float $totalAmount,
        float $taxRate
    ): array {

        if ($totalAmount < 0 || $taxRate < 0) {
            throw new \InvalidArgumentException(
                'Le montant TTC et le taux de taxe doivent être positifs.'
            );
        }

        $baseAmount = $totalAmount / (1 + ($taxRate / 100));

        $taxAmount = $totalAmount - $baseAmount;

        return [
            'base_amount' => round($baseAmount, 2),
            'tax_rate' => $taxRate,
            'tax_amount' => round($taxAmount, 2),
            'total_amount' => round($totalAmount, 2)
        ];
    }

    /**
     * Calculer plusieurs taxes sur une même base.
     */
    public function calculateMultipleTaxes(
        float $baseAmount,
        array $taxRates
    ): array {

        if ($baseAmount < 0) {
            throw new \InvalidArgumentException(
                'La base imposable doit être positive.'
            );
        }

        $taxes = [];
        $totalTax = 0;

        foreach ($taxRates as $tax) {

            $rate = (float) ($tax['rate'] ?? 0);

            $amount = $this->calculateTax(
                $baseAmount,
                $rate
            );

            $taxes[] = [
                'name' => $tax['name'] ?? '',
                'rate' => $rate,
                'amount' => $amount
            ];

            $totalTax += $amount;
        }

        return [
            'base_amount' => round($baseAmount, 2),
            'taxes' => $taxes,
            'total_tax' => round($totalTax, 2),
            'total_amount' => round(
                $baseAmount + $totalTax,
                2
            )
        ];
    }

    private function isValidDate(string $date): bool
    {
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}
