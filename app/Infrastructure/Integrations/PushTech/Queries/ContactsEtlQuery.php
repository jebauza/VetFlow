<?php

namespace App\ParqueSantiago\Syschronize\PushTech\Etl\Queries;

use App\Infrastructure\Integrations\PushTech\Queries\PushTechQuery;

class ContactsEtlQuery extends PushTechQuery
{
    public function __construct()
    {
        $this->path = '/v2/account/:account_id/contact';
        $this->query_parameters = [
            'limit' => 200, // limit of number campaigns returns, default: 200
            'order' => 'desc', // creation_date order asc or desc, default: desc
        ];
    }

    public function set_limit(int $limit)
    {
        $this->query_parameters['limit'] = $limit;

        return $this;
    }

    public function set_order(string $order = 'desc')
    {
        $this->query_parameters['order'] = $order;

        return $this;
    }

    public function set_from_id(string $contact_id = null)
    {
        if ($contact_id) {
            $this->query_parameters['from_id'] = $contact_id;
        } else {
            unset($this->query_parameters['from_id']);
        }

        return $this;
    }

    public function set_contact_id_from(string $contact_id_from = null)
    {
        if ($contact_id_from) {
            $this->query_parameters['contact_id_from'] = $contact_id_from;
        } else {
            unset($this->query_parameters['contact_id_from']);
        }

        return $this;
    }
}
