<?php
/**
 * Debug script to diagnose customer sync issues
 * Upload to FacturaScripts root and access via browser
 */

require_once 'config.php';

use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Dinamic\Model\Cliente;

$dataBase = new DataBase();

echo "<html><head><title>WooSync Customer Sync Debug</title>";
echo "<style>body{font-family:sans-serif;margin:20px;}h2{color:#333;border-bottom:2px solid #007bff;padding-bottom:5px;}pre{background:#f4f4f4;padding:10px;border:1px solid #ddd;overflow:auto;}.success{color:green;}.error{color:red;}.warning{color:orange;}</style>";
echo "</head><body>";
echo "<h1>WooSync Customer Sync Diagnostic</h1>";

// Test 1: Check paises table
echo "<h2>Test 1: Countries in Database</h2>";
try {
    $sql = "SELECT codpais, nombre FROM paises ORDER BY codpais LIMIT 20";
    $countries = $dataBase->select($sql);
    if (empty($countries)) {
        echo "<p class='error'>❌ No countries found in paises table!</p>";
        echo "<p>This is the problem! FacturaScripts needs countries in the database.</p>";
    } else {
        echo "<p class='success'>✅ Found " . count($countries) . " countries (showing first 20):</p>";
        echo "<pre>";
        foreach ($countries as $country) {
            echo "{$country['codpais']} - {$country['nombre']}\n";
        }
        echo "</pre>";
    }
} catch (\Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

// Test 2: Check specific country codes
echo "<h2>Test 2: Check Common Country Codes</h2>";
$testCodes = ['ESP', 'ES', 'USA', 'US', 'GBR', 'GB', 'FRA', 'FR'];
foreach ($testCodes as $code) {
    try {
        $sql = "SELECT codpais, nombre FROM paises WHERE codpais = " . $dataBase->var2str($code);
        $result = $dataBase->select($sql);
        if (!empty($result)) {
            echo "<p class='success'>✅ {$code}: " . $result[0]['nombre'] . "</p>";
        } else {
            echo "<p class='warning'>⚠ {$code}: Not found</p>";
        }
    } catch (\Exception $e) {
        echo "<p class='error'>❌ {$code}: Error - " . $e->getMessage() . "</p>";
    }
}

// Test 3: Try creating a test customer
echo "<h2>Test 3: Test Customer Creation</h2>";
try {
    $cliente = new Cliente();
    $cliente->codcliente = 'TEST' . rand(100, 999);
    $cliente->nombre = 'Test Customer';
    $cliente->email = 'test@example.com';
    
    // Try to set country
    $sql = "SELECT codpais FROM paises LIMIT 1";
    $result = $dataBase->select($sql);
    if (!empty($result)) {
        $cliente->codpais = $result[0]['codpais'];
        echo "<p>Using country code: {$cliente->codpais}</p>";
    } else {
        echo "<p class='error'>No countries available to set!</p>";
    }
    
    echo "<p>Attempting to save test customer...</p>";
    if ($cliente->save()) {
        echo "<p class='success'>✅ Test customer saved successfully!</p>";
        echo "<p>Customer code: {$cliente->codcliente}</p>";
        
        // Clean up
        $cliente->delete();
        echo "<p>Test customer deleted.</p>";
    } else {
        echo "<p class='error'>❌ Failed to save test customer!</p>";
        if (method_exists($cliente, 'getErrors')) {
            $errors = $cliente->getErrors();
            echo "<p>Errors:</p><pre>";
            print_r($errors);
            echo "</pre>";
        }
        echo "<p>Cliente object state:</p><pre>";
        echo "codcliente: {$cliente->codcliente}\n";
        echo "nombre: {$cliente->nombre}\n";
        echo "email: {$cliente->email}\n";
        echo "codpais: {$cliente->codpais}\n";
        echo "</pre>";
    }
} catch (\Exception $e) {
    echo "<p class='error'>❌ Exception: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . ":" . $e->getLine() . "</p>";
}

// Test 4: Check Cliente table structure
echo "<h2>Test 4: Cliente Table Structure</h2>";
try {
    $sql = "DESCRIBE clientes";
    $columns = $dataBase->select($sql);
    echo "<pre>";
    foreach ($columns as $col) {
        $required = ($col['Null'] === 'NO') ? '(REQUIRED)' : '';
        $default = !empty($col['Default']) ? "(default: {$col['Default']})" : '';
        echo "{$col['Field']} - {$col['Type']} {$required} {$default}\n";
    }
    echo "</pre>";
} catch (\Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

// Test 5: Check for unique constraints
echo "<h2>Test 5: Check Constraints</h2>";
try {
    $sql = "SELECT CONSTRAINT_NAME, CONSTRAINT_TYPE FROM information_schema.table_constraints WHERE table_schema = schema() AND table_name = 'clientes'";
    $constraints = $dataBase->select($sql);
    if (!empty($constraints)) {
        echo "<pre>";
        foreach ($constraints as $constraint) {
            echo "{$constraint['CONSTRAINT_TYPE']}: {$constraint['CONSTRAINT_NAME']}\n";
        }
        echo "</pre>";
    }
} catch (\Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h2>Summary</h2>";
echo "<p>If you see errors above, they explain why customer sync is failing.</p>";
echo "<p><strong>Common fixes:</strong></p>";
echo "<ul>";
echo "<li>If no countries found: FacturaScripts needs to be initialized with country data</li>";
echo "<li>If test customer fails: Check required fields and constraints</li>";
echo "<li>If specific error messages: Follow those to fix the issue</li>";
echo "</ul>";

echo "<p><strong>After fixing issues, delete this file for security!</strong></p>";
echo "</body></html>";
