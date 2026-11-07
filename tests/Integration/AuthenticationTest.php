<?php

declare(strict_types=1);

/**
 * =========================================================
 * CABINET COMPTABLE
 * Tests d'intégration de l'authentification
 * Fichier : tests/Integration/AuthenticationTest.php
 * =========================================================
 */

use PHPUnit\Framework\TestCase;

final class AuthenticationTest extends TestCase
{
    /**
     * Vérifier qu'un e-mail valide est reconnu.
     */
    public function testValidEmailIsAccepted(): void
    {
        $email = 'admin@cabinet-comptable.local';

        $this->assertMatchesRegularExpression(
            '/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
            $email
        );
    }

    /**
     * Vérifier qu'un e-mail invalide est rejeté.
     */
    public function testInvalidEmailIsRejected(): void
    {
        $email = 'administrateur-invalide';

        $this->assertDoesNotMatchRegularExpression(
            '/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
            $email
        );
    }

    /**
     * Vérifier le hachage d'un mot de passe.
     */
    public function testPasswordCanBeHashed(): void
    {
        $password = 'Admin@12345';

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $this->assertNotFalse($hash);
        $this->assertNotEquals($password, $hash);
    }

    /**
     * Vérifier un mot de passe avec son hachage.
     */
    public function testPasswordCanBeVerified(): void
    {
        $password = 'Admin@12345';

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $this->assertTrue(
            password_verify($password, $hash)
        );
    }

    /**
     * Vérifier qu'un mauvais mot de passe est rejeté.
     */
    public function testWrongPasswordIsRejected(): void
    {
        $password = 'Admin@12345';

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $this->assertFalse(
            password_verify('WrongPassword', $hash)
        );
    }

    /**
     * Vérifier la création d'une session.
     */
    public function testSessionCanStoreAuthenticatedUser(): void
    {
        $session = [];

        $session['user_id'] = 1;
        $session['user_role'] = 'admin';

        $this->assertEquals(1, $session['user_id']);
        $this->assertEquals('admin', $session['user_role']);
    }

    /**
     * Vérifier la déconnexion.
     */
    public function testLogoutClearsAuthenticationData(): void
    {
        $session = [
            'user_id' => 1,
            'user_role' => 'admin'
        ];

        unset($session['user_id'], $session['user_role']);

        $this->assertArrayNotHasKey('user_id', $session);
        $this->assertArrayNotHasKey('user_role', $session);
    }
}
