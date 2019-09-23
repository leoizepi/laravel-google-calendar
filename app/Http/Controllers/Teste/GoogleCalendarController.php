<?php

namespace App\Http\Controllers\Teste;

use App\Service\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\GoogleCalendar\Event;

class GoogleCalendarController extends Controller
{
    public function index()
    {
        //create a new event
        $event = new Event();

        $event->name = 'Teste Criar Evento';
        $event->startDateTime = Carbon::now();
        $event->endDateTime = Carbon::now()->addHour();
        $event->addAttendee(['email' => 'leoizepi@gmail.com']);
        $event->addAttendee(['email' => 'crm@digicomm.com.br']);
        $event->save();

        dd('script finalizado');
    }


    public function index2()
    {
        $client = new \Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->setScopes(\Google_Service_Calendar::CALENDAR_EVENTS);

        $service = new \Google_Service_Calendar($client);
        $calendarId = 'primary';

        $attendees = array(
            array('email' => 'leoizepi@gmail.com'),
            //array('email' => 'crm@digicomm.com.br'),
        );
        $event = new \Google_Service_Calendar_Event(array(
            'summary' => 'teste',
            'description' => 'teste',
            'start' => array(
                'dateTime' => Carbon::now(),
                'timeZone' => 'America/Sao_Paulo',
            ),
            'end' => array(
                'dateTime' => Carbon::now()->addHour(),
                'timeZone' => 'America/Sao_Paulo',
            ),
            'attendees' => $attendees,
        ));
        $event_result = $service->events->insert($calendarId, $event);


        dd('script finalizado', $event_result);
    }

    public function exemploOficial()
    {
        //$data_evento = '2019-09-24T17:00:00-07:00';

        $event = new \Google_Service_Calendar_Event(array(
            'summary' => 'Lenardo teste',
            'location' => '800 Howard St., San Francisco, CA 94103',
            'description' => 'Leonardo Teste Description',
            'start' => array(
                'dateTime' => '2019-09-24T09:00:00-07:00',
                'timeZone' => 'America/Sao_Paulo',
            ),
            'end' => array(
                'dateTime' => '2019-09-24T17:00:00-07:00',
                'timeZone' => 'America/Sao_Paulo',
            ),
            'recurrence' => array(
                'RRULE:FREQ=DAILY;COUNT=2'
            ),
            'attendees' => array(
                array('email' => 'leoizepi@gmail.com'),
                array('email' => 'crm@digicomm.com.br'),
            ),
            'reminders' => array(
                'useDefault' => FALSE,
                'overrides' => array(
                    array('method' => 'email', 'minutes' => 24 * 60),
                    array('method' => 'popup', 'minutes' => 10),
                ),
            ),
        ));

        $client = new \Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->setScopes(\Google_Service_Calendar::CALENDAR);

        $service = new \Google_Service_Calendar($client);
        $calendarId = 'primary';
        $event = $service->events->insert($calendarId, $event);

        $event_last = Event::get()->first();
        dd($event->htmlLink, $event->description, $event_last);

        printf('Event created: %s\n', $event->htmlLink);
    }
}
