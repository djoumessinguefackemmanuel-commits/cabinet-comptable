<?php

/**
 * ==========================================================
 * GESTION DES PERMISSIONS
 * Projet : Cabinet Comptable
 * ==========================================================
 */

return [

    'admin' => [

        'dashboard',
        'users.view',
        'users.create',
        'users.edit',
        'users.delete',
        'roles.manage',
        'companies.view',
        'companies.create',
        'companies.edit',
        'companies.delete',
        'exercises.view',
        'exercises.create',
        'exercises.edit',
        'exercises.close',
        'accounts.view',
        'accounts.create',
        'accounts.edit',
        'journals.view',
        'journals.create',
        'journals.edit',
        'entries.view',
        'entries.create',
        'entries.edit',
        'entries.validate',
        'entries.delete',
        'ledger.view',
        'balance.view',
        'taxes.view',
        'taxes.manage',
        'reports.view',
        'reports.export',
        'documents.view',
        'documents.manage',
        'invoices.view',
        'invoices.create',
        'invoices.edit',
        'invoices.delete',
        'notifications.view',
        'audit.view',
        'settings.manage'

    ],

    'expert' => [

        'dashboard',
        'users.view',
        'companies.view',
        'companies.create',
        'companies.edit',
        'exercises.view',
        'exercises.create',
        'exercises.edit',
        'exercises.close',
        'accounts.view',
        'accounts.create',
        'accounts.edit',
        'journals.view',
        'journals.create',
        'journals.edit',
        'entries.view',
        'entries.create',
        'entries.edit',
        'entries.validate',
        'ledger.view',
        'balance.view',
        'taxes.view',
        'taxes.manage',
        'reports.view',
        'reports.export',
        'documents.view',
        'documents.manage',
        'invoices.view',
        'notifications.view',
        'audit.view'

    ],

    'accountant' => [

        'dashboard',
        'companies.view',
        'exercises.view',
        'accounts.view',
        'journals.view',
        'entries.view',
        'entries.create',
        'entries.edit',
        'ledger.view',
        'balance.view',
        'taxes.view',
        'reports.view',
        'reports.export',
        'documents.view',
        'documents.manage',
        'invoices.view',
        'notifications.view'

    ],

    'client' => [

        'dashboard',
        'companies.view',
        'exercises.view',
        'reports.view',
        'documents.view',
        'invoices.view',
        'notifications.view'

    ]

];
