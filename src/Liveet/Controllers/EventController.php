<?php

namespace Liveet\Controllers;

use Rashtell\Domain\JSON;
use Liveet\Domain\Constants;
use Liveet\Models\EventModel;
use Liveet\Domain\MailHandler;
use Liveet\Controllers\BaseController;
use Liveet\Models\AdminActivityLogModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class EventController extends HelperController
{

    /** Admin User */

    public function createEvent(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "created an event"]);

        $this->checkAdminEventPermission($request, $response);

        return $this->createSelf(
            $request,
            $response,
            new EventModel(),
            [
                "required" => [
                    "organiser_id",
                    "event_name", "event_desc", "event_type", "event_venue", "event_date_time", "event_payment_type",
                    "tickets",
                    "event_can_invite", "event_sale_stop_time", "event_can_transfer_ticket", "event_can_recall"
                ],

                "expected" => [
                    "event_name", "event_desc", "event_multimedia", "event_type", "event_venue", "event_date_time", "event_payment_type", "organiser_id",
                    "tickets"
                    // => [
                    //     "ticket_name", "ticket_desc", "ticket_cost", "ticket_population", "ticket_discount",
                    // ]
                    ,
                    "event_can_invite", "event_sale_stop_time", "event_can_transfer_ticket", "event_can_recall",
                ],
            ],
            [
                "imageOptions" => [
                    ["imageKey" => "event_multimedia"]
                ]
            ],
        );
    }

    public function getEvents(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $this->checkAdminEventPermission($request, $response);

        $expectedRouteParams = ["event_id", "event_code", "event_type", "payment_type", "organiser_id"];
        $routeParams = $this->getRouteParams($request);
        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        return $this->getByPage($request, $response, new EventModel(), null, $conditions, ["eventTickets", "eventControl"]);
    }

    public function getEventByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $this->checkAdminEventPermission($request, $response);

        return $this->getByPK($request, $response, new EventModel(), null, ["eventTickets", "eventControl"]);
    }

    public function updateEventByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "updated an event details"]);

        $this->checkAdminEventPermission($request, $response);

        return $this->updateByPK(
            $request,
            $response,
            new EventModel(),
            [
                "required" => [
                    "event_name", "event_desc", "event_type", "event_venue", "event_date_time", "event_payment_type",
                    "tickets",
                    "event_can_invite", "event_sale_stop_time", "event_can_transfer_ticket", "event_can_recall"
                ],

                "expected" => [
                    "event_name", "event_desc", "event_multimedia", "event_type", "event_venue", "event_date_time", "event_payment_type", "organiser_id",
                    "tickets"
                    // => [
                    //     "ticket_name", "ticket_desc", "ticket_cost", "ticket_population", "ticket_discount",
                    // ]
                    ,
                    "event_can_invite", "event_sale_stop_time", "event_can_transfer_ticket", "event_can_recall",
                ]
            ]
        );
    }

    public function deleteEventByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "deletd an event"]);

        $this->checkAdminEventPermission($request, $response);

        return $this->deleteByPK($request, $response, (new EventModel()));
    }

    /** Organiser Staff */

    public function getOrganiserEvents(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $this->checkOrganiserEventPermission($request, $response);
        $organiser_id = $authDetails["organiser_id"];
        $expectedRouteParams = ["event_id", "event_code", "event_type", "payment_type"];
        $routeParams = $this->getRouteParams($request);
        $conditions = ["organiser_id" => $organiser_id];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        return $this->getByPage($request, $response, new EventModel(), null, $conditions, ["eventTickets", "eventControl"]);
    }
}
