<?php

namespace App\Controllers;

use App\Http\Request;
use App\Services\ExportService;

class ExportController extends Controller
{
    private ExportService $export;

    public function __construct()
    {
        $this->export = new ExportService();
    }

    public function vigilantesXls(Request $request)
    {
        $this->export->vigilantesXls($request->query());
    }

    public function vigilantesPdf(Request $request)
    {
        $this->export->vigilantesPdf($request->query());
    }

    public function supervisoresXls(Request $request)
    {
        $this->export->supervisoresXls();
    }

    public function supervisoresPdf(Request $request)
    {
        $this->export->supervisoresPdf();
    }

    public function vigiasXls(Request $request)
    {
        $this->export->vigiasXls();
    }

    public function vigiasPdf(Request $request)
    {
        $this->export->vigiasPdf();
    }
}

