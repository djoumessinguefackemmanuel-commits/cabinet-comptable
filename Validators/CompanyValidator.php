
<?php

class CompanyValidator
{
    /**
     * Valider la création d'une entreprise.
     */
    public static function validateCreate(array $data): array
    {
        $validator = new Validator();

        $validator->required('name', $data['name'] ?? '');
        $validator->min('name', $data['name'] ?? '', 2);
        $validator->max('name', $data['name'] ?? '', 150);

        $validator->required('email', $data['email'] ?? '');
        $validator->email('email', $data['email'] ?? '');

        $validator->required('phone', $data['phone'] ?? '');
        $validator->min('phone', $data['phone'] ?? '', 8);
        $validator->max('phone', $data['phone'] ?? '', 20);

        $validator->required('address', $data['address'] ?? '');
        $validator->max('address', $data['address'] ?? '', 255);

        $validator->required('city', $data['city'] ?? '');
        $validator->max('city', $data['city'] ?? '', 100);

        if (!empty($data['tax_number'])) {
            $validator->max('tax_number', $data['tax_number'], 50);
        }

        if (!empty($data['registration_number'])) {
            $validator->max(
                'registration_number',
                $data['registration_number'],
                50
            );
        }

        return [
            'valid' => !$validator->hasErrors(),
            'errors' => $validator->getErrors()
        ];
    }

    /**
     * Valider la modification d'une entreprise.
     */
    public static function validateUpdate(array $data): array
    {
        return self::validateCreate($data);
    }
}
