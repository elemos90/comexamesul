<?php
// TESTE ISOLADO - NÃO DEPENDE DE NADA
header('Content-Type: text/plain; charset=utf-8');
echo "PHP está funcionando!\n";
echo "Versão: " . phpversion() . "\n";
echo "Servidor: " . php_sapi_name() . "\n";
echo "Data: " . date('Y-m-d H:i:s') . "\n";
