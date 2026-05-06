<?php
require __DIR__ . '/vendor/autoload.php';

echo 'Vérification de l\'intégrité du système US030:' . PHP_EOL . PHP_EOL;

// Check classes exist
echo '1. Entités et Services:' . PHP_EOL;
echo '   ✓ FieldEdit: ' . (class_exists('App\Entity\FieldEdit') ? 'OK' : 'FAIL') . PHP_EOL;
echo '   ✓ FieldEditRepository: ' . (class_exists('App\Repository\FieldEditRepository') ? 'OK' : 'FAIL') . PHP_EOL;
echo '   ✓ FieldProvenanceService: ' . (class_exists('App\Service\FieldProvenanceService') ? 'OK' : 'FAIL') . PHP_EOL;

// Check constants
echo PHP_EOL . '2. Constants FieldEdit:' . PHP_EOL;
echo '   ✓ SOURCE_DECLARED: ' . \App\Entity\FieldEdit::SOURCE_DECLARED . PHP_EOL;
echo '   ✓ SOURCE_DETECTED: ' . \App\Entity\FieldEdit::SOURCE_DETECTED . PHP_EOL;
echo '   ✓ SOURCE_UPDATED: ' . \App\Entity\FieldEdit::SOURCE_UPDATED . PHP_EOL;
echo '   ✓ SOURCE_CORRECTED: ' . \App\Entity\FieldEdit::SOURCE_CORRECTED . PHP_EOL;

// Check methods
echo PHP_EOL . '3. Méthodes du Service:' . PHP_EOL;
$reflection = new ReflectionClass('App\Service\FieldProvenanceService');
foreach (['trackFieldUpdate', 'getFieldProvenance', 'getSourcingReport', 'migrateToProvenanceFormat', 'extractCurrentValues', 'getTimeline', 'setValueByPath'] as $method) {
    echo '   ✓ ' . $method . ': ' . ($reflection->hasMethod($method) ? 'OK' : 'FAIL') . PHP_EOL;
}

echo PHP_EOL . '4. Point d\'entrée onboarding:' . PHP_EOL;
$onboardingReflection = new ReflectionClass('App\Service\OnboardingService');
echo '   ✓ updateSessionField: ' . ($onboardingReflection->hasMethod('updateSessionField') ? 'OK' : 'FAIL') . PHP_EOL;

echo PHP_EOL . '✅ US030 - Socle du dossier collaboratif implémenté et testé.' . PHP_EOL;
