<?php

namespace EventEspresso\WpUser\domain\services\graphql;

use EE_Error;
use EE_Ticket;
use EventEspresso\core\services\graphql\fields\GraphQLFieldInterface;
use EventEspresso\core\services\graphql\fields\GraphQLField;
use GraphQL\Type\Definition\ResolveInfo;
use ReflectionException;
use WPGraphQL\AppContext;

/**
 * Class RegisterSchema
 * Description
 *
 * @package EventEspresso\WpUser\domain\services\graphql
 * @author  Manzoor Wani
 * @since   2.1.0.p
 */
class RegisterSchema
{
    /**
     * @return void
     * @since 2.1.0.p
     */
    public function addHooks()
    {
        add_filter(
            'FHEE__EventEspresso_core_domain_services_graphql_types__ticket_fields',
            [$this, 'registerCoreTicketFields']
        );

        add_action(
            'AHEE__EventEspresso_core_domain_services_graphql_mutators_ticket_create',
            [$this, 'updateTicketCapMeta'],
            10,
            2
        );

        add_action(
            'AHEE__EventEspresso_core_domain_services_graphql_mutators_ticket_update',
            [$this, 'updateTicketCapMeta'],
            10,
            2
        );
    }


    /**
     * @param GraphQLFieldInterface[] $fields
     * @return GraphQLFieldInterface[]
     * @since 2.1.0.p
     */
    public function registerCoreTicketFields(array $fields)
    {
        $newFields = [
            // add ticketCap field to ticket schema
            new GraphQLField(
                'capabilityRequired',
                'String',
                null,
                esc_html__('WP User Capability required for purchasing this ticket.', 'event_espresso'),
                null,
                [$this, 'getCapabilityRequired']
            ),
        ];

        return array_merge($fields, $newFields);
    }


    /**
     * @param mixed       $source  The source that's passed down the GraphQL queries
     * @param array       $args    The inputArgs on the field
     * @param AppContext  $context The AppContext passed down the GraphQL tree
     * @param ResolveInfo $info    The ResolveInfo passed down the GraphQL tree
     * @return string
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function getCapabilityRequired($source, array $args, AppContext $context, ResolveInfo $info)
    {
        return $source instanceof EE_Ticket
            ? $source->get_extra_meta('ee_ticket_cap_required', true, '')
            : '';
    }


    /**
     * @param EE_Ticket $ticket The ticket being mutated.
     * @param array     $input  Data coming from the GraphQL mutation query input.
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function updateTicketCapMeta(EE_Ticket $ticket, array $input)
    {
        // capabilityRequired can be empty as well
        if (array_key_exists('capabilityRequired', $input)) {
            $capabilityRequired = sanitize_text_field($input['capabilityRequired']);
            $ticket->update_extra_meta('ee_ticket_cap_required', $capabilityRequired);
        }
    }
}
