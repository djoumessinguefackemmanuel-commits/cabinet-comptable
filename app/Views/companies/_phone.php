<?php
use App\Services\PhoneNumber;

[$phoneCode, $phoneLocal] = PhoneNumber::split($phoneValue ?? '');
?>
<label for="phone" class="form-label">
    Téléphone *
</label>

<div class="input-group">
    <select name="phone_code" id="phone_code" class="form-select" style="max-width: 190px" aria-label="Indicatif du pays">
        <optgroup label="Afrique">
            <?php foreach (PhoneNumber::AFRICA as $code => $label): ?>
                <option value="<?= e($code) ?>" <?= $code === $phoneCode ? 'selected' : '' ?>>
                    <?= e($label) ?> (<?= e($code) ?>)
                </option>
            <?php endforeach; ?>
        </optgroup>
        <optgroup label="Autres pays">
            <?php foreach (PhoneNumber::OTHERS as $code => $label): ?>
                <option value="<?= e($code) ?>" <?= $code === $phoneCode ? 'selected' : '' ?>>
                    <?= e($label) ?> (<?= e($code) ?>)
                </option>
            <?php endforeach; ?>
        </optgroup>
    </select>

    <input
        type="tel"
        id="phone"
        name="phone"
        class="form-control"
        inputmode="tel"
        autocomplete="tel-national"
        placeholder="6 70 00 00 00"
        value="<?= e($phoneLocal) ?>"
        required
    >
</div>
<div class="form-text">Choisissez le pays : l'indicatif est ajouté automatiquement au numéro.</div>
