<?php
namespace App\Service;


use App\ChamadoTarefa;
use App\Usuario;

class GoogleCalendarService
{

    public function getClient()
    {
        $client = new \Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->setScopes(\Google_Service_Calendar::CALENDAR);

        return $client;
    }


    public function enviarParaTecnicos(ChamadoTarefa $chamado_tarefa)
    {
        $chamado = $chamado_tarefa->chamado;
        $cliente = $chamado->cliente;

        // endereco
        $endereco = $cliente->endereco;
        $endereco_full = "$endereco->end_endereco $endereco->end_numero $endereco->end_complemento, $endereco->end_cep - $endereco->end_bairro, $endereco->end_cidade/$endereco->end_estado";

        // emails
        $tecnicos_ids = $chamado_tarefa->tecnicos->pluck('chatt_usu_id')->toArray();
        $emails = Usuario::whereIn('usu_id', $tecnicos_ids)->get()->pluck('usu_email')->toArray();
        $attendees = array();
        foreach ($emails as $email) {
            $attendees[] = array('email' => $email);
        }

        // evitar criar eventos em ambientes não produção
        if(env('APP_ENV') !== "production") {
            $attendees = array(
                array('email' => 'leoizepi@gmail.com'),
                array('email' => 'leonardo@digicomm.com.br'),
            );
        }

        // data e hora
        $data_hora_start = $chamado_tarefa->chat_previsao_data->format('Y-m-d').'T'.$chamado_tarefa->chat_previsao_hora;
        if($chamado_tarefa->chat_previsao_hora_fim) {
            $data_hora_end   = $chamado_tarefa->chat_previsao_data->format('Y-m-d').'T'.$chamado_tarefa->chat_previsao_hora_fim;
        } else {
            $data_hora_end = $data_hora_start;
        }

        $client = $this->getClient();
        $service = new \Google_Service_Calendar($client);
        $calendarId = 'primary';
        $event = new \Google_Service_Calendar_Event(array(
            'summary' => "$cliente->cli_nome_fantasia, Chamado Nº $chamado->cha_id",
            'location' => $endereco_full,
            'description' => $chamado_tarefa->chat_descricao,
            'start' => array(
                'dateTime' => $data_hora_start,
                'timeZone' => 'America/Sao_Paulo',
            ),
            'end' => array(
                'dateTime' => $data_hora_end,
                'timeZone' => 'America/Sao_Paulo',
            ),
            'attendees' => $attendees,
        ));

        $calendar_event_id = $chamado_tarefa->chat_google_calendar_event_id;
        // insert
        if(empty($calendar_event_id)) {
            $event_result = $service->events->insert($calendarId, $event);
            $calendar_event_id = $event_result->id;
            $chamado_tarefa->chat_google_calendar_event_id = $calendar_event_id;
            $chamado_tarefa->save();
        }
        // update
        else {
            $event_result = $service->events->update($calendarId, $calendar_event_id, $event);
        }
        return $event_result;
    }

    public function remover($chamado_tarefa)
    {
        $calendar_event_id = $chamado_tarefa->chat_google_calendar_event_id;
        if(!empty($calendar_event_id)) {
            $client = $this->getClient();
            $service = new \Google_Service_Calendar($client);
            $calendarId = 'primary';
            $event_result = $service->events->delete($calendarId, $calendar_event_id);
            //dd($event_result);

            $chamado_tarefa->chat_google_calendar_event_id = null;
            $chamado_tarefa->save();
        }
        return true;
    }

}