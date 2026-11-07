<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * Afficher les paramètres de l'application.
     */
    public function index(): void
    {
        $settings = Setting::getAll();

        $this->view('admin.settings.index', [
            'settings' => $settings
        ]);
    }

    /**
     * Enregistrer les modifications des paramètres.
     */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(url('settings'));
            return;
        }

        $settings = $_POST['settings'] ?? [];

        if (
            isset($_POST['setting_key'], $_POST['setting_value'])
            && is_string($_POST['setting_key'])
            && is_string($_POST['setting_value'])
        ) {
            $settings = [
                trim($_POST['setting_key']) => trim($_POST['setting_value'])
            ];
        }

        if (!is_array($settings) || empty($settings)) {
            Session::set('error', 'Aucun paramètre à modifier.');
            $this->redirect(url('settings'));
            return;
        }

        foreach ($settings as $key => $value) {
            $key = trim((string) $key);
            $value = trim((string) $value);

            if ($key === '') {
                continue;
            }

            if (!Setting::set($key, $value)) {
                Session::set('error', 'Une erreur est survenue lors de la modification des paramètres.');
                $this->redirect(url('settings'));
                return;
            }
        }

        Session::set('success', 'Paramètres modifiés avec succès.');

        $this->redirect(url('settings'));
    }
}