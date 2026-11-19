<?php
/**
 * Plan comptable SYSCOHADA révisé (OHADA) : comptes principaux et divisionnaires usuels.
 * Format : numéro => intitulé. Le type (actif, passif, capitaux, charge, produit) est déduit du numéro.
 */
return [
    // Classe 1 - Ressources durables
    '10' => 'Capital', '101' => 'Capital social', '104' => 'Primes liées au capital social', '105' => 'Écarts de réévaluation',
    '106' => 'Réserves', '1061' => 'Réserve légale', '1068' => 'Autres réserves', '11' => 'Report à nouveau',
    '12' => 'Résultat de l\'exercice', '121' => 'Résultat en instance d\'affectation : bénéfice', '129' => 'Résultat en instance d\'affectation : perte',
    '13' => 'Résultat net de l\'exercice', '131' => 'Résultat net : bénéfice', '139' => 'Résultat net : perte',
    '14' => 'Subventions d\'investissement', '15' => 'Provisions réglementées et fonds assimilés',
    '16' => 'Emprunts et dettes assimilées', '161' => 'Emprunts obligataires', '162' => 'Emprunts et dettes auprès des établissements de crédit',
    '165' => 'Dépôts et cautionnements reçus', '17' => 'Dettes de crédit-bail et contrats assimilés', '18' => 'Dettes liées à des participations et comptes de liaison',
    '19' => 'Provisions pour risques et charges',
    // Classe 2 - Actif immobilisé
    '21' => 'Immobilisations incorporelles', '211' => 'Frais de développement', '212' => 'Brevets, licences, logiciels', '213' => 'Fonds commercial',
    '22' => 'Terrains', '23' => 'Bâtiments, installations techniques et agencements', '231' => 'Bâtiments industriels, agricoles et commerciaux',
    '24' => 'Matériel, mobilier et actifs biologiques', '241' => 'Matériel et outillage industriel', '244' => 'Matériel et mobilier de bureau',
    '245' => 'Matériel de transport', '246' => 'Matériel informatique', '25' => 'Avances et acomptes versés sur immobilisations',
    '26' => 'Titres de participation', '27' => 'Autres immobilisations financières', '271' => 'Prêts et créances', '275' => 'Dépôts et cautionnements versés',
    '28' => 'Amortissements', '281' => 'Amortissements des immobilisations incorporelles', '283' => 'Amortissements des bâtiments',
    '284' => 'Amortissements du matériel', '29' => 'Dépréciations des immobilisations',
    // Classe 3 - Stocks
    '31' => 'Marchandises', '32' => 'Matières premières et fournitures liées', '33' => 'Autres approvisionnements', '34' => 'Produits en cours',
    '35' => 'Services en cours', '36' => 'Produits finis', '37' => 'Produits intermédiaires et résiduels', '38' => 'Stocks en cours de route, en consignation ou en dépôt',
    '39' => 'Dépréciations des stocks',
    // Classe 4 - Tiers
    '40' => 'Fournisseurs et comptes rattachés', '401' => 'Fournisseurs, dettes en compte', '402' => 'Fournisseurs, effets à payer',
    '408' => 'Fournisseurs, factures non parvenues', '409' => 'Fournisseurs débiteurs',
    '41' => 'Clients et comptes rattachés', '411' => 'Clients', '412' => 'Clients, effets à recevoir', '416' => 'Clients douteux ou litigieux',
    '418' => 'Clients, produits non encore facturés', '419' => 'Clients créditeurs',
    '42' => 'Personnel', '421' => 'Personnel, avances et acomptes', '422' => 'Personnel, rémunérations dues', '428' => 'Personnel, charges à payer',
    '43' => 'Organismes sociaux', '431' => 'Sécurité sociale (CNPS)', '433' => 'Autres organismes sociaux',
    '44' => 'État et collectivités publiques', '441' => 'État, impôt sur les bénéfices', '442' => 'État, autres impôts et taxes',
    '4431' => 'TVA facturée sur ventes', '4432' => 'TVA facturée sur prestations de services', '4451' => 'TVA récupérable sur immobilisations',
    '4452' => 'TVA récupérable sur achats', '4453' => 'TVA récupérable sur transport', '4454' => 'TVA récupérable sur services extérieurs',
    '4441' => 'État, TVA due', '4449' => 'État, crédit de TVA à reporter', '447' => 'État, impôts retenus à la source', '449' => 'État, créances et dettes diverses',
    '45' => 'Organismes internationaux', '46' => 'Associés et groupe', '461' => 'Associés, opérations sur le capital', '462' => 'Associés, comptes courants',
    '47' => 'Débiteurs et créditeurs divers', '471' => 'Débiteurs et créditeurs divers', '476' => 'Charges constatées d\'avance', '477' => 'Produits constatés d\'avance',
    '478' => 'Écarts de conversion actif', '479' => 'Écarts de conversion passif', '48' => 'Créances et dettes hors activités ordinaires',
    '49' => 'Dépréciations et provisions pour risques à court terme',
    // Classe 5 - Trésorerie
    '50' => 'Titres de placement', '51' => 'Valeurs à encaisser', '511' => 'Effets à encaisser', '513' => 'Chèques à encaisser',
    '52' => 'Banques', '521' => 'Banques locales', '522' => 'Banques autres États de la zone', '53' => 'Établissements financiers et assimilés',
    '54' => 'Instruments de trésorerie', '56' => 'Banques, crédits de trésorerie et d\'escompte', '57' => 'Caisse', '571' => 'Caisse siège',
    '572' => 'Caisse succursale', '58' => 'Régies d\'avances, accréditifs et virements internes', '585' => 'Virements de fonds', '59' => 'Dépréciations et provisions pour risques à court terme',
    // Classe 6 - Charges des activités ordinaires
    '60' => 'Achats et variations de stocks', '601' => 'Achats de marchandises', '602' => 'Achats de matières premières', '604' => 'Achats stockés de matières et fournitures consommables',
    '605' => 'Autres achats', '6031' => 'Variation des stocks de marchandises', '608' => 'Achats d\'emballages',
    '61' => 'Transports', '611' => 'Transports sur achats', '612' => 'Transports sur ventes', '618' => 'Autres frais de transport',
    '62' => 'Services extérieurs A', '621' => 'Sous-traitance générale', '622' => 'Locations et charges locatives', '623' => 'Redevances de crédit-bail',
    '624' => 'Entretien, réparations et maintenance', '625' => 'Primes d\'assurance', '626' => 'Études, recherches et documentation', '627' => 'Publicité, publications, relations publiques',
    '628' => 'Frais de télécommunications', '63' => 'Services extérieurs B', '631' => 'Frais bancaires', '632' => 'Rémunérations d\'intermédiaires et de conseils',
    '633' => 'Frais de formation du personnel', '637' => 'Rémunérations du personnel extérieur', '638' => 'Autres charges externes',
    '64' => 'Impôts et taxes', '641' => 'Impôts et taxes directs', '645' => 'Impôts et taxes indirects', '646' => 'Droits d\'enregistrement', '647' => 'Pénalités et amendes fiscales',
    '65' => 'Autres charges', '651' => 'Pertes sur créances clients', '658' => 'Charges diverses',
    '66' => 'Charges de personnel', '661' => 'Rémunérations directes versées au personnel national', '662' => 'Rémunérations directes versées au personnel non national',
    '663' => 'Indemnités forfaitaires versées au personnel', '664' => 'Charges sociales', '668' => 'Autres charges sociales',
    '67' => 'Frais financiers et charges assimilées', '671' => 'Intérêts des emprunts', '672' => 'Intérêts dans loyers de crédit-bail', '676' => 'Pertes de change', '678' => 'Autres charges financières',
    '68' => 'Dotations aux amortissements', '681' => 'Dotations aux amortissements d\'exploitation', '69' => 'Dotations aux provisions et dépréciations', '691' => 'Dotations aux provisions d\'exploitation',
    // Classe 7 - Produits des activités ordinaires
    '70' => 'Ventes', '701' => 'Ventes de marchandises', '702' => 'Ventes de produits finis', '703' => 'Ventes de produits intermédiaires', '704' => 'Travaux facturés',
    '705' => 'Travaux et études facturés', '706' => 'Services vendus', '707' => 'Produits accessoires',
    '71' => 'Subventions d\'exploitation', '72' => 'Production immobilisée', '73' => 'Variations de stocks de biens et de services produits',
    '75' => 'Autres produits', '751' => 'Profits sur créances clients', '758' => 'Produits divers', '77' => 'Revenus financiers et produits assimilés',
    '771' => 'Intérêts de prêts', '776' => 'Gains de change', '78' => 'Transferts de charges', '79' => 'Reprises de provisions et dépréciations',
    // Classe 8 - Autres charges et autres produits (HAO)
    '81' => 'Valeurs comptables des cessions d\'immobilisations', '82' => 'Produits des cessions d\'immobilisations', '83' => 'Charges hors activités ordinaires',
    '84' => 'Produits hors activités ordinaires', '85' => 'Dotations hors activités ordinaires', '86' => 'Reprises hors activités ordinaires',
    '87' => 'Participation des travailleurs', '88' => 'Subventions d\'équilibre', '89' => 'Impôts sur le résultat', '891' => 'Impôts sur les bénéfices de l\'exercice',
];
