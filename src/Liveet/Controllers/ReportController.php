<?php

namespace Liveet\Controllers;

use Liveet\Models\OrganiserModel;
use Liveet\Models\OrganiserStaffModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReportController extends HelperController
{

    /** Admin Report */

    public function getOrganiserSummary(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminReportPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        return $this->getDashboardByPK($request, $response, new OrganiserModel());
    }
}
