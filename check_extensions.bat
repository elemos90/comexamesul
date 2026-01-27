@echo off
echo ============================================
echo Verificador de Extensoes PHP - Melhorias
echo ============================================
echo.

echo [1] Verificando versao do PHP...
php -v
echo.

echo [2] Verificando extensao ZIP...
php -m | findstr /i "zip"
if %ERRORLEVEL% EQU 0 (
    echo [OK] Extensao ZIP esta habilitada!
) else (
    echo [ERRO] Extensao ZIP NAO encontrada!
    echo.
    echo Solucao:
    echo 1. Abra: C:\xampp\php\php.ini
    echo 2. Procure: ;extension=zip
    echo 3. Remova o ponto-e-virgula: extension=zip
    echo 4. Salve e reinicie Apache
)
echo.

echo [3] Verificando extensao PDO...
php -m | findstr /i "pdo"
echo.

echo [4] Verificando Composer...
composer --version
echo.

echo ============================================
echo Pressione qualquer tecla para sair...
pause >nul
