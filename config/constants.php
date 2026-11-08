<?php

/**
 * ==========================================================
 * CONSTANTES DE L'APPLICATION
 * Projet : Cabinet Comptable
 * Architecture : MVC
 * ==========================================================
 */

// Rôles des utilisateurs
define('ROLE_ADMIN', 'admin');
define('ROLE_EXPERT', 'expert');
define('ROLE_ACCOUNTANT', 'accountant');
define('ROLE_CLIENT', 'client');

// Statuts des comptes utilisateurs
define('USER_ACTIVE', 'active');
define('USER_INACTIVE', 'inactive');
define('USER_SUSPENDED', 'suspended');

// Statuts des exercices comptables
define('EXERCISE_OPEN', 'open');
define('EXERCISE_CLOSED', 'closed');
define('EXERCISE_LOCKED', 'locked');

// Statuts des écritures comptables
define('ENTRY_DRAFT', 'draft');
define('ENTRY_VALIDATED', 'validated');
define('ENTRY_CANCELLED', 'cancelled');

// Types de comptes comptables
define('ACCOUNT_ASSET', 'asset');
define('ACCOUNT_LIABILITY', 'liability');
define('ACCOUNT_EQUITY', 'equity');
define('ACCOUNT_EXPENSE', 'expense');
define('ACCOUNT_REVENUE', 'revenue');

// Types de journaux comptables
define('JOURNAL_PURCHASE', 'purchase');
define('JOURNAL_SALES', 'sales');
define('JOURNAL_BANK', 'bank');
define('JOURNAL_CASH', 'cash');
define('JOURNAL_GENERAL', 'general');

// Statuts des factures
define('INVOICE_DRAFT', 'draft');
define('INVOICE_PENDING', 'pending');
define('INVOICE_PAID', 'paid');
define('INVOICE_CANCELLED', 'cancelled');

// Statuts des notifications
define('NOTIFICATION_READ', 'read');
define('NOTIFICATION_UNREAD', 'unread');

// Types de documents
define('DOCUMENT_INVOICE', 'invoice');
define('DOCUMENT_RECEIPT', 'receipt');
define('DOCUMENT_SUPPORTING', 'supporting_document');

// Limites des fichiers
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

// Extensions de fichiers autorisées
define('ALLOWED_FILE_EXTENSIONS', [
    'pdf',
    'jpg',
    'jpeg',
    'png',
    'doc',
    'docx',
    'xls',
    'xlsx'
]);
