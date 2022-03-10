<?php

namespace EventEspresso\WpUser\domain\entities\routing;

use EE_Error;
use EE_Extra_Meta;
use EEM_Extra_Meta;
use EventEspresso\core\domain\services\graphql\Utilities;
use EventEspresso\core\services\json\JsonDataNode;
use EventEspresso\core\services\json\JsonDataNodeValidator;

/**
 * Class WpUserData
 * injects data into the EventEditor DOM data
 *
 * @author  Brent Christensen
 * @package EventEspresso\WpUser\domain\entities\routing
 * @since   2.1.0.p
 */
class WpUserData extends JsonDataNode
{

    const NODE_NAME = 'wpUserData';

    /**
     * @var array
     */
    private $ticket_meta_data = [];

    /**
     * @var Utilities
     */
    private $utilities;


    /**
     * WpUserData JsonDataNode constructor.
     *
     * @param JsonDataNodeValidator $validator
     * @param Utilities             $utilities
     */
    public function __construct(JsonDataNodeValidator $validator, Utilities $utilities)
    {
        parent::__construct($validator);
        $this->utilities = $utilities;
        $this->setNodeName(WpUserData::NODE_NAME);
        add_filter(
            'FHEE__EventEspresso_core_domain_entities_routing_data_nodes_domains_EventEditor__initialize__related_data',
            [$this, 'getTicketCapabilitiesRequired']
        );
    }


    /**
     * @param array $ticket_meta_data
     */
    public function setTicketMetaData(array $ticket_meta_data): void
    {
        $this->ticket_meta_data = $ticket_meta_data;
    }


    /**
     * @return void
     */
    public function initialize()
    {
        $this->addCapabilityOptions();
        $this->addTicketMetaData();
    }


    /**
     * @return void
     */
    private function addCapabilityOptions()
    {
        $capability_options = [
            __('Standard', 'event_espresso') => [
                'none' => __('none', 'event_espresso'),
                'read' => __('Read Capabilities', 'event_espresso'),
            ],
        ];

        if (defined('WS_PLUGIN__S2MEMBER_MIN_WP_VERSION')) {
            $capability_options['s2Member'] = [
                'access_s2member_level0' => __('Level 0 Member', 'event_espresso'),
                'access_s2member_level1' => __('Level 1 Member', 'event_espresso'),
                'access_s2member_level2' => __('Level 2 Member', 'event_espresso'),
                'access_s2member_level3' => __('Level 3 Member', 'event_espresso'),
                'access_s2member_level4' => __('Level 4 Member', 'event_espresso'),
            ];
        }

        $this->addData(
            'capabilityOptions',
            apply_filters(
                'FHEE__EventEspresso_WpUser_domain_entities_routing_WpUserData__initialize__capabilityOptions',
                $capability_options
            )
        );
    }


    /**
     * @return void
     */
    private function addTicketMetaData()
    {
        $this->addData(
            'ticketsMeta',
            apply_filters(
                'FHEE__EventEspresso_WpUser_domain_entities_routing_WpUserData__initialize__capabilityOptions',
                $this->ticket_meta_data
            )
        );
    }


    /**
     * @param array $event_editor_gql_data
     * @return array
     * @throws EE_Error
     */
    public function getTicketCapabilitiesRequired(array $event_editor_gql_data): array
    {
        if (isset($event_editor_gql_data['tickets']['nodes'])) {
            $ticket_meta_data = [];
            foreach ($event_editor_gql_data['tickets']['nodes'] as $key => $ticket_node) {
                $extra_meta = isset($ticket_node['dbId'])
                    ? EEM_Extra_Meta::instance()->get_one(
                        [
                            [
                                'OBJ_ID'   => $ticket_node['dbId'],
                                'EXM_type' => 'Ticket',
                                'EXM_key'  => 'ee_ticket_cap_required',
                            ],
                        ]
                    )
                    : null;
                if ($extra_meta instanceof EE_Extra_Meta) {
                    $ticket_meta_data[ $ticket_node['id'] ] = ['capabilityRequired' => $extra_meta->value()];
                }
            }
            $this->setTicketMetaData($ticket_meta_data);
        }
        return $event_editor_gql_data;
    }
}
