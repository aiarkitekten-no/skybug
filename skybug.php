<?php
/*
 * Plugin Name: SkyBug – Bug & Feature Tracker
 * Plugin URI:  https://smartesider.no/skybug
 * Description: SkyBug sporer kjente feil (bugs) og ønskede funksjoner for ulike programmer direkte i WP-admin.
 * Version:     1.4.0
 * Author:      SmartesiderDev
 * Text Domain: skybug
 * Domain Path: /languages
 */

// Hindre direkte aksess
if (!defined('ABSPATH')) {
    exit;
}

// Definer konstanter
define('SKYBUG_DIR', plugin_dir_path(__FILE__));
define('SKYBUG_URL', plugin_dir_url(__FILE__));
define('SKYBUG_VERSION', '1.4.0');

// Last text domain for oversettelser
add_action('plugins_loaded', 'skybug_load_textdomain');

# a1b2c3d4 - Last text domain for oversettelser - se AI-learned/funksjonslogg.json
function skybug_load_textdomain() {
    load_plugin_textdomain('skybug', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
# slutt a1b2c3d4

// Registrer Custom Post Types og Taxonomier
add_action('init', 'skybug_register_post_types');
add_action('init', 'skybug_register_issue_status');

# 1a2b3c4d - Registrer Custom Post Types og Taxonomier - se AI-learned/funksjonslogg.json
function skybug_register_post_types() {
    // Registrer Program CPT
    register_post_type('skybug_program', array(
        'labels' => array(
            'name' => __('Programmer', 'skybug'),
            'singular_name' => __('Program', 'skybug'),
            'add_new_item' => __('Legg til nytt program', 'skybug'),
            'edit_item' => __('Rediger program', 'skybug'),
            'menu_name' => __('Programmer', 'skybug'),
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => false, // vi håndterer meny manuelt
        'supports' => array('title','editor','thumbnail'),
        'capability_type' => 'post',
        'map_meta_cap' => true,
        'has_archive' => false,
        'rewrite' => false,
    ));

    // Registrer Sak CPT
    register_post_type('skybug_issue', array(
        'labels' => array(
            'name' => __('Saker', 'skybug'),
            'singular_name' => __('Sak', 'skybug'),
            'add_new_item' => __('Ny sak', 'skybug'),
            'edit_item' => __('Rediger sak', 'skybug'),
            'menu_name' => __('Saker', 'skybug'),
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => false, // vi håndterer meny manuelt
        'supports' => array('title','editor','comments'),
        'capability_type' => 'post',
        'map_meta_cap' => true,
        'has_archive' => false,
        'rewrite' => false,
    ));

    // Registrer taksonomi for sakstype
    register_taxonomy('skybug_type','skybug_issue', array(
        'labels' => array(
            'name' => __('Sakstype', 'skybug'),
            'singular_name' => __('Sakstype', 'skybug'),
            'menu_name' => __('Type', 'skybug'),
        ),
        'public' => false,
        'show_ui' => true,
        'hierarchical' => false,
        'show_in_menu' => false,
    ));

    // Registrer taksonomi for programtype
    register_taxonomy('skybug_program_type','skybug_program', array(
        'labels' => array(
            'name' => __('Programtype', 'skybug'),
            'singular_name' => __('Programtype', 'skybug'),
            'menu_name' => __('Type', 'skybug'),
        ),
        'public' => false,
        'show_ui' => true,
        'hierarchical' => false,
        'show_in_menu' => false,
    ));

    // Opprett standard termer for sakstype hvis de ikke finnes
    if (!term_exists(__('Bug','skybug'), 'skybug_type')) {
        wp_insert_term(__('Bug','skybug'), 'skybug_type', array('slug'=>'bug'));
    }
    if (!term_exists(__('Ønsket funksjon','skybug'), 'skybug_type')) {
        wp_insert_term(__('Ønsket funksjon','skybug'), 'skybug_type', array('slug'=>'feature'));
    }

    // Opprett standard termer for programtype hvis de ikke finnes
    $program_types = array(
        'web' => array('name' => __('Web App','skybug'), 'icon' => '🌐'),
        'mobile' => array('name' => __('Mobile App','skybug'), 'icon' => '📱'),
        'desktop' => array('name' => __('Desktop App','skybug'), 'icon' => '🖥️'),
        'api' => array('name' => __('API','skybug'), 'icon' => '🔌'),
        'service' => array('name' => __('Service','skybug'), 'icon' => '⚙️'),
        'database' => array('name' => __('Database','skybug'), 'icon' => '🗄️')
    );
    
    foreach ($program_types as $slug => $type) {
        if (!term_exists($type['name'], 'skybug_program_type')) {
            wp_insert_term($type['name'], 'skybug_program_type', array('slug' => $slug));
            // Store icon as term meta
            $term = get_term_by('slug', $slug, 'skybug_program_type');
            if ($term) {
                update_term_meta($term->term_id, 'skybug_type_icon', $type['icon']);
            }
        }
    }
}
# slutt 1a2b3c4d

// Registrer lukket status for saker
# k2l3m4n5 - Registrer custom post status skybug_closed - se AI-learned/funksjonslogg.json
function skybug_register_issue_status() {
    // Eksisterende lukket status
    register_post_status('skybug_closed', array(
        'label' => _x('Lukket', 'post status', 'skybug'),
        'public' => false,
        'internal' => false,
        'protected' => false,
        'exclude_from_search' => true,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Lukket <span class="count">(%s)</span>', 'Lukket <span class="count">(%s)</span>', 'skybug')
    ));
    
    // Nye statuser for bedre ticket management
    register_post_status('skybug_in_progress', array(
        'label' => _x('Under arbeid', 'post status', 'skybug'),
        'public' => false,
        'internal' => false,
        'protected' => false,
        'exclude_from_search' => true,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Under arbeid <span class="count">(%s)</span>', 'Under arbeid <span class="count">(%s)</span>', 'skybug')
    ));
    
    register_post_status('skybug_waiting', array(
        'label' => _x('Venter på svar', 'post status', 'skybug'),
        'public' => false,
        'internal' => false,
        'protected' => false,
        'exclude_from_search' => true,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Venter på svar <span class="count">(%s)</span>', 'Venter på svar <span class="count">(%s)</span>', 'skybug')
    ));
    
    register_post_status('skybug_resolved', array(
        'label' => _x('Løst - venter på bekreftelse', 'post status', 'skybug'),
        'public' => false,
        'internal' => false,
        'protected' => false,
        'exclude_from_search' => true,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Løst <span class="count">(%s)</span>', 'Løst <span class="count">(%s)</span>', 'skybug')
    ));
}
# slutt k2l3m4n5

// Legg til statusvelger i innsendingsboks for skybug_issue
# l3m4n5o6 - Legg til status UI i submit box - se AI-learned/funksjonslogg.json
add_action('post_submitbox_misc_actions', function(){
    global $post;
    if(!$post || $post->post_type !== 'skybug_issue') { return; }
    $current = $post->post_status;
    echo '<div class="misc-pub-section skybug-status"><label for="skybug_issue_status"><span class="dashicons dashicons-yes"></span> ' . esc_html__('Status','skybug') . '</label><br/>';
    echo '<select name="skybug_issue_status" id="skybug_issue_status">';
    $options = array(
        'publish' => __('Ny/Åpen','skybug'),
        'skybug_in_progress' => __('Under arbeid','skybug'),
        'skybug_waiting' => __('Venter på svar','skybug'),
        'skybug_resolved' => __('Løst - venter på bekreftelse','skybug'),
        'skybug_closed' => __('Lukket','skybug')
    );
    foreach($options as $val=>$label) {
        echo '<option value="' . esc_attr($val) . '" ' . selected($current,$val,false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select></div>';
});
# slutt l3m4n5o6

// Lagre valgt status når saken oppdateres
# m4n5o6p7 - Lagre status fra UI - se AI-learned/funksjonslogg.json
add_action('save_post_skybug_issue', function($post_id, $post){
    if (!isset($_POST['skybug_issue_status'])) { return; }
    if (!current_user_can('edit_post',$post_id)) { return; }
    // Hvis ingen reell endring, avslutt tidlig
    if($post->post_status === $_POST['skybug_issue_status']) { return; }
    
    $allowed_statuses = ['publish', 'skybug_in_progress', 'skybug_waiting', 'skybug_resolved', 'skybug_closed'];
    $new_status = in_array($_POST['skybug_issue_status'], $allowed_statuses) ? $_POST['skybug_issue_status'] : 'publish';
    
    if ($post->post_status !== $new_status) {
        // Log status endring som intern kommentar
        $old_status = $post->post_status;
        $status_labels = [
            'publish' => __('Ny/Åpen','skybug'),
            'skybug_in_progress' => __('Under arbeid','skybug'),
            'skybug_waiting' => __('Venter på svar','skybug'),
            'skybug_resolved' => __('Løst - venter på bekreftelse','skybug'),
            'skybug_closed' => __('Lukket','skybug')
        ];
        $skip_comment = (get_post_meta($post_id,'_skybug_last_status_change_source',true)==='ajax');
        if($skip_comment){ delete_post_meta($post_id,'_skybug_last_status_change_source'); }
        if(!$skip_comment){
            $comment = sprintf(
                __('Status endret fra "%s" til "%s"', 'skybug'),
                $status_labels[$old_status] ?? $old_status,
                $status_labels[$new_status] ?? $new_status
            );
        }
        
        // Oppdater status
        remove_action('save_post_skybug_issue', __FUNCTION__ ,10); // hindre loop
        wp_update_post(array('ID'=>$post_id,'post_status'=>$new_status));
        add_action('save_post_skybug_issue', __FUNCTION__ ,10,2);
        
    // Legg til intern kommentar om status endring hvis ikke AJAX allerede logget
    if(!$skip_comment){ skybug_add_internal_comment($post_id, $comment); }
    // Invalider metrics cache
    delete_transient('skybug_metrics_cache_v2_b');
    delete_transient('skybug_metrics_cache_v2_d');
        
        // Lagre timestamp for siste oppdatering
        update_post_meta($post_id, '_skybug_last_activity', current_time('timestamp'));
    }
},10,2);
# slutt m4n5o6p7

// Interne kommentarer system
function skybug_add_internal_comment($post_id, $comment, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $internal_comments = get_post_meta($post_id, '_skybug_internal_comments', true) ?: [];
    $internal_comments[] = [
        'comment' => $comment,
        'user_id' => $user_id,
        'timestamp' => current_time('timestamp'),
        'date' => current_time('mysql')
    ];
    
    update_post_meta($post_id, '_skybug_internal_comments', $internal_comments);
}

function skybug_get_internal_comments($post_id) {
    return get_post_meta($post_id, '_skybug_internal_comments', true) ?: [];
}
# slutt internal comments

// Meta boxes for ticket management
add_action('add_meta_boxes', 'skybug_add_ticket_meta_boxes');
function skybug_add_ticket_meta_boxes() {
    add_meta_box(
        'skybug_ticket_info',
        __('Ticket Informasjon', 'skybug'),
        'skybug_ticket_info_meta_box',
        'skybug_issue',
        'side',
        'high'
    );
    add_meta_box(
        'skybug_latest_email',
        __('Siste E-post', 'skybug'),
        'skybug_latest_email_meta_box',
        'skybug_issue',
        'side',
        'high'
    );
}

function skybug_ticket_info_meta_box($post) {
    $priority = get_post_meta($post->ID, '_skybug_priority', true) ?: 'medium';
    $assigned_user = get_post_meta($post->ID, '_skybug_assigned_user', true);
    $reporter_name = get_post_meta($post->ID, '_skybug_reporter_name', true);
    $reporter_email = get_post_meta($post->ID, '_skybug_reporter_email', true);
    $last_activity = get_post_meta($post->ID, '_skybug_last_activity', true);
    $current_type = wp_get_post_terms($post->ID, 'skybug_type');
    $selected_type = !empty($current_type) ? $current_type[0]->slug : '';

    echo '<div class="skybug-ticket-info">';
    echo '<p><strong>' . __('Ticket ID:', 'skybug') . '</strong> #' . $post->ID . '</p>';

    echo '<p>'; 
    echo '<label for="skybug_type"><strong>' . __('Sakstype:', 'skybug') . '</strong></label><br>';
    echo '<select name="skybug_type" id="skybug_type" class="skybug-full-width">';
    echo '<option value="">' . __('-- Velg type --', 'skybug') . '</option>';
    echo '<option value="bug"' . selected($selected_type, 'bug', false) . '>🐛 ' . __('Feilrapport', 'skybug') . '</option>';
    echo '<option value="feature"' . selected($selected_type, 'feature', false) . '>💡 ' . __('Ønsket funksjon', 'skybug') . '</option>';
    echo '</select>';
    echo '<small class="skybug-help-small">' . __('Velg "Feilrapport" eller "Ønsket funksjon" for å kategorisere saken', 'skybug') . '</small>';
    echo '</p>';

    echo '<p>';
    echo '<label for="skybug_priority"><strong>' . __('Prioritet:', 'skybug') . '</strong></label><br>';
    echo '<select name="skybug_priority" id="skybug_priority" class="skybug-full-width">';
    $priorities = [
        'low' => __('Lav', 'skybug'),
        'medium' => __('Middels', 'skybug'),
        'high' => __('Høy', 'skybug'),
        'critical' => __('Kritisk', 'skybug')
    ];
    foreach ($priorities as $value => $label) {
        echo '<option value="' . esc_attr($value) . '"' . selected($priority, $value, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
    echo '</p>';

    echo '<p>';
    echo '<label for="skybug_assigned_user"><strong>' . __('Tildelt:', 'skybug') . '</strong></label><br>';
    wp_dropdown_users(array(
        'name' => 'skybug_assigned_user',
        'id' => 'skybug_assigned_user',
        'selected' => $assigned_user,
        'show_option_none' => __('-- Ikke tildelt --', 'skybug'),
        'option_none_value' => '',
        'class' => 'widefat'
    ));
    echo '</p>';
    
    // Assigned user
    echo '<p>';
    echo '<label for="skybug_assigned_user"><strong>' . __('Tildelt:', 'skybug') . '</strong></label><br>';
    wp_dropdown_users(array(
        'name' => 'skybug_assigned_user',
        'id' => 'skybug_assigned_user',
        'selected' => $assigned_user,
        'show_option_none' => __('-- Ikke tildelt --', 'skybug'),
        'option_none_value' => '',
        'class' => 'widefat'
    ));
    echo '</p>';
    
    // Reporter info
    if ($reporter_name || $reporter_email) {
    echo '<div class="skybug-box-muted">';
        echo '<strong>' . __('Melder:', 'skybug') . '</strong><br>';
        if ($reporter_name) {
            echo '<strong>' . esc_html($reporter_name) . '</strong><br>';
        }
        if ($reporter_email) {
            echo '<a href="mailto:' . esc_attr($reporter_email) . '">' . esc_html($reporter_email) . '</a>';
        }
        echo '</div>';
    }
    
    // Last activity
    if ($last_activity) {
    echo '<p class="skybug-last-activity">';
        echo '<strong>' . __('Sist oppdatert:', 'skybug') . '</strong> ';
        echo human_time_diff($last_activity, current_time('timestamp')) . __(' siden', 'skybug');
        echo '</p>';
    }
    
    echo '</div>';
}

// Internal comments meta box
function skybug_internal_comments_meta_box($post) {
    $comments = skybug_get_internal_comments($post->ID);
    
    echo '<div class="skybug-internal-comments">';
    
    // Add new comment form
    echo '<div class="skybug-add-comment">';
    echo '<textarea name="skybug_new_comment" placeholder="' . esc_attr__('Legg til intern kommentar...', 'skybug') . '" rows="3"></textarea>';
    echo '<p style="margin-top:5px"><em>' . __('Interne kommentarer er kun synlige for administratorer.', 'skybug') . '</em></p>';
    echo '</div>';
    
    // Display existing comments
    if (!empty($comments)) {
        echo '<div class="skybug-comments-list">';
        foreach (array_reverse($comments) as $comment) {
            $user = get_user_by('id', $comment['user_id']);
            $username = $user ? $user->display_name : __('Ukjent bruker', 'skybug');
            
            echo '<div class="skybug-comment">';
            echo '<div class="skybug-comment-meta">';
            echo '<strong>' . esc_html($username) . '</strong> - ';
            echo human_time_diff(strtotime($comment['date']), current_time('timestamp')) . __(' siden', 'skybug');
            echo '</div>';
            echo '<div class="skybug-comment-content">' . nl2br(esc_html($comment['comment'])) . '</div>';
            echo '</div>';
        }
        echo '</div>';
    } else {
    echo '<p class="skybug-comment-empty">' . __('Ingen interne kommentarer ennå.', 'skybug') . '</p>';
    }
    
    echo '</div>';
}

// Communication meta box
function skybug_communication_meta_box($post) {
    $reporter_email = get_post_meta($post->ID, '_skybug_reporter_email', true);
    $reporter_name = get_post_meta($post->ID, '_skybug_reporter_name', true);
    
    echo '<div class="skybug-communication">';
    
    if ($reporter_email) {
    echo '<div class="skybug-email-form">';
    echo '<h4>' . __('Send E-post til Melder', 'skybug') . '</h4>';
        
        echo '<p>';
        echo '<label for="email_to"><strong>' . __('Til:', 'skybug') . '</strong></label><br>';
    echo '<input type="email" id="email_to" value="' . esc_attr($reporter_email) . '" readonly class="skybug-email-readonly">';
        echo '</p>';
        
        echo '<p>';
        echo '<label for="email_subject"><strong>' . __('Emne:', 'skybug') . '</strong></label><br>';
    echo '<input type="text" id="email_subject" name="skybug_email_subject" value="' . esc_attr(sprintf(__('Ang. sak #%d: %s', 'skybug'), $post->ID, $post->post_title)) . '" class="skybug-email-full">';
        echo '</p>';
        
        echo '<p>';
        echo '<label for="email_message"><strong>' . __('Melding:', 'skybug') . '</strong></label><br>';
    echo '<textarea id="email_message" name="skybug_email_message" rows="8" class="skybug-email-full" placeholder="' . esc_attr__('Skriv melding til melder...', 'skybug') . '"></textarea>';
        echo '</p>';
        
        echo '<button type="button" id="send_email_to_reporter" class="button button-primary">' . __('Send E-post', 'skybug') . '</button>';
        echo '</div>';
    } else {
    echo '<p class="skybug-email-empty-msg">' . __('Ingen melder e-post registrert for denne saken.', 'skybug') . '</p>';
    echo '<div class="skybug-warning-box">';
        echo '<strong>' . __('Tips:', 'skybug') . '</strong> ' . __('For saker som kommer via IMAP vil melder-informasjon bli automatisk registrert.', 'skybug');
        echo '</div>';
    }
    
    echo '</div>';
}

// Latest email meta box - NYTT
function skybug_latest_email_meta_box($post) {
    $imap_enabled = get_option('skybug_imap_enabled', false);
    $imap_configured = !empty(get_option('skybug_imap_host')) && !empty(get_option('skybug_imap_username'));
    $last_check = get_option('skybug_last_imap_check', 0);
    
    echo '<div class="skybug-latest-email">';
    
    // IMAP Status
    echo '<div class="skybug-center-block">';
    if ($imap_enabled && $imap_configured) {
    echo '<span class="skybug-imap-green">🟢</span>';
    echo '<p class="skybug-imap-status-green">' . __('IMAP Aktiv', 'skybug') . '</p>';
        if ($last_check) {
            $time_ago = human_time_diff($last_check, current_time('timestamp'));
            echo '<small style="color: #666;">' . sprintf(__('Sist sjekket: %s siden', 'skybug'), $time_ago) . '</small>';
        }
    } elseif ($imap_configured) {
    echo '<span class="skybug-imap-yellow">🟡</span>';
    echo '<p class="skybug-imap-status-yellow">' . __('IMAP Konfigurert', 'skybug') . '</p>';
        echo '<small style="color: #666;">' . __('Ikke aktivert', 'skybug') . '</small>';
    } else {
    echo '<span class="skybug-imap-red">🔴</span>';
    echo '<p class="skybug-imap-status-red">' . __('IMAP Ikke konfigurert', 'skybug') . '</p>';
    }
    echo '</div>';
    
    // Manual email check button
    if ($imap_enabled && $imap_configured) {
        echo '<div style="text-align: center; margin-bottom: 15px;">';
        echo '<button type="button" class="button button-secondary" id="check-imap-emails" data-post-id="' . $post->ID . '">' . __('Hent nye e-poster', 'skybug') . '</button>';
        echo '</div>';
        
        // Result area
        echo '<div id="imap-check-result" style="margin-top: 10px;"></div>';
        
        // Recent emails for this issue
        echo '<div style="background: #f9f9f9; padding: 10px; border-radius: 4px; margin-top: 15px;">';
        echo '<h4 style="margin-top: 0;">' . __('Relaterte E-poster', 'skybug') . '</h4>';
        
        $emails = get_post_meta($post->ID, '_skybug_related_emails', true);
        if (!empty($emails) && is_array($emails)) {
            foreach (array_slice($emails, -3) as $email) { // Show last 3 emails
                echo '<div style="border-left: 3px solid #007cba; padding-left: 8px; margin-bottom: 8px; font-size: 12px;">';
                echo '<strong>Fra:</strong> ' . esc_html($email['from']) . '<br>';
                echo '<strong>Emne:</strong> ' . esc_html($email['subject']) . '<br>';
                echo '<strong>Dato:</strong> ' . date('d.m.Y H:i', $email['date']);
                echo '</div>';
            }
        } else {
            echo '<p style="color: #666; font-style: italic; font-size: 12px;">' . __('Ingen relaterte e-poster funnet', 'skybug') . '</p>';
        }
        
        echo '</div>';
    } else {
        echo '<div style="background: #fff3cd; padding: 10px; border-radius: 4px; border-left: 4px solid #ffc107;">';
        echo '<strong>' . __('Info:', 'skybug') . '</strong><br>';
        echo __('Konfigurer IMAP under Innstillinger for å kunne motta e-poster automatisk og knytte dem til saker.', 'skybug');
        echo '</div>';
    }
    
    echo '</div>';
}

// Save ticket meta data
add_action('save_post_skybug_issue', 'skybug_save_ticket_meta', 5, 2);
function skybug_save_ticket_meta($post_id, $post) {
    // Verify nonce
    if (!isset($_POST['skybug_ticket_meta_nonce']) || !wp_verify_nonce($_POST['skybug_ticket_meta_nonce'], 'skybug_ticket_meta')) {
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Avoid autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Save priority
    if (isset($_POST['skybug_priority'])) {
        $priorities = ['low', 'medium', 'high', 'critical'];
        $priority = in_array($_POST['skybug_priority'], $priorities) ? $_POST['skybug_priority'] : 'medium';
        update_post_meta($post_id, '_skybug_priority', $priority);
    }
    
    // Save assigned user
    if (isset($_POST['skybug_assigned_user'])) {
        $assigned_user = intval($_POST['skybug_assigned_user']);
        if ($assigned_user > 0) {
            update_post_meta($post_id, '_skybug_assigned_user', $assigned_user);
        } else {
            delete_post_meta($post_id, '_skybug_assigned_user');
        }
    }
    
    // Save type/category - NYTT
    if (isset($_POST['skybug_type'])) {
        $selected_type = sanitize_text_field($_POST['skybug_type']);
        if (in_array($selected_type, ['bug', 'feature'])) {
            // Set the term for this post
            wp_set_post_terms($post_id, array($selected_type), 'skybug_type');
        } else {
            // Clear the term if empty selection
            wp_set_post_terms($post_id, array(), 'skybug_type');
        }
    }
    
    // Save new internal comment
    if (!empty($_POST['skybug_new_comment'])) {
        $comment = sanitize_textarea_field($_POST['skybug_new_comment']);
        if (!empty(trim($comment))) {
            skybug_add_internal_comment($post_id, $comment);
            
            // Update last activity timestamp
            update_post_meta($post_id, '_skybug_last_activity', current_time('timestamp'));
        }
    }
}

// AJAX handler for sending email to reporter
add_action('wp_ajax_skybug_send_reporter_email', 'skybug_send_reporter_email');
function skybug_send_reporter_email() {
    if (!current_user_can('manage_options')) {
        wp_die(json_encode(['success' => false, 'message' => 'Insufficient permissions']));
    }
    
    $post_id = intval($_POST['post_id']);
    $subject = sanitize_text_field($_POST['subject']);
    $message = sanitize_textarea_field($_POST['message']);
    
    $reporter_email = get_post_meta($post_id, '_skybug_reporter_email', true);
    if (!$reporter_email) {
        wp_die(json_encode(['success' => false, 'message' => 'Ingen melder e-post funnet']));
    }
    
    // Send email using SMTP if configured
    $result = skybug_send_email($reporter_email, $subject, $message);
    
    if ($result) {
        // Log communication as internal comment
        $current_user = wp_get_current_user();
        $comment = sprintf(
            __('E-post sendt til %s av %s\nEmne: %s', 'skybug'),
            $reporter_email,
            $current_user->display_name,
            $subject
        );
        skybug_add_internal_comment($post_id, $comment);
        
        // Update last activity
        update_post_meta($post_id, '_skybug_last_activity', current_time('timestamp'));
        
        wp_die(json_encode(['success' => true, 'message' => 'E-post sendt til ' . $reporter_email]));
    } else {
        wp_die(json_encode(['success' => false, 'message' => 'Feil ved sending av e-post']));
    }
}

// Varsle webhook ved lukking
# n5o6p7q8 - Webhook dispatch ved lukking av sak - se AI-learned/funksjonslogg.json
add_action('transition_post_status', function($new, $old, $post){
    if($post->post_type !== 'skybug_issue') { return; }
    if($old === $new) { return; }
    if($new !== 'skybug_closed') { return; }
    $program_id = get_post_meta($post->ID, '_skybug_program_id', true);
    if(!$program_id) { return; }
    $webhook = get_post_meta($program_id, '_skybug_webhook_url', true);
    if(!$webhook) { return; }
    $payload = array(
        'event' => 'issue_closed',
        'issue_id' => $post->ID,
        'issue_title' => $post->post_title,
        'program_id' => (int)$program_id,
        'timestamp' => time()
    );
    skybug_dispatch_webhook($program_id, $payload);
},10,3);
# slutt n5o6p7q8

// Option: notify email (lagres i Innstillinger siden via options API midlertidig felt)
# o6p7q8r9 - Legg til felt for notify email og SMTP/IMAP i Innstillinger - se AI-learned/funksjonslogg.json
add_action('admin_init', function(){
    // Varslinger seksjon
    register_setting('skybug_settings_group','skybug_notify_email', array(
        'type'=>'string','sanitize_callback'=>'sanitize_email','default'=>''
    ));
    add_settings_section('skybug_main_section', __('Varslinger','skybug'), function(){
        echo '<p>' . esc_html__('Konfigurer epost for varsling ved nye saker.','skybug') . '</p>';
    }, 'skybug_settings_page');
    add_settings_field('skybug_notify_email_field', __('Varslings-epost','skybug'), function(){
        $val = get_option('skybug_notify_email','');
        echo '<input type="email" name="skybug_notify_email" value="' . esc_attr($val) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__('Hvis tomt sendes ingen epostvarsler.','skybug') . '</p>';
    }, 'skybug_settings_page','skybug_main_section');
    
    // SMTP seksjon
    add_settings_section('skybug_smtp_section', __('SMTP Konfigurasjon','skybug'), function(){
        echo '<p>' . esc_html__('Konfigurer SMTP for å sende e-post via ekstern e-postserver.','skybug') . '</p>';
    }, 'skybug_settings_page');
    
    // SMTP Host
    register_setting('skybug_settings_group','skybug_smtp_host', array(
        'type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>''
    ));
    add_settings_field('skybug_smtp_host_field', __('SMTP Server','skybug'), function(){
        $val = get_option('skybug_smtp_host','');
        echo '<input type="text" name="skybug_smtp_host" value="' . esc_attr($val) . '" class="regular-text" placeholder="smtp.gmail.com" />';
        echo '<p class="description">' . esc_html__('SMTP server hostname eller IP-adresse.','skybug') . '</p>';
    }, 'skybug_settings_page','skybug_smtp_section');
    
    // SMTP Port
    register_setting('skybug_settings_group','skybug_smtp_port', array(
        'type'=>'integer','sanitize_callback'=>'absint','default'=>587
    ));
    add_settings_field('skybug_smtp_port_field', __('SMTP Port','skybug'), function(){
        $val = get_option('skybug_smtp_port', 587);
        echo '<input type="number" name="skybug_smtp_port" value="' . esc_attr($val) . '" min="1" max="65535" />';
        echo '<p class="description">' . esc_html__('Standard: 587 (TLS) eller 465 (SSL).','skybug') . '</p>';
    }, 'skybug_settings_page','skybug_smtp_section');
    
    // SMTP Username
    register_setting('skybug_settings_group','skybug_smtp_username', array(
        'type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>''
    ));
    add_settings_field('skybug_smtp_username_field', __('SMTP Brukernavn','skybug'), function(){
        $val = get_option('skybug_smtp_username','');
        echo '<input type="text" name="skybug_smtp_username" value="' . esc_attr($val) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__('Vanligvis samme som e-postadresse.','skybug') . '</p>';
    }, 'skybug_settings_page','skybug_smtp_section');
    
    // SMTP Password
    register_setting('skybug_settings_group','skybug_smtp_password', array(
        'type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>''
    ));
    add_settings_field('skybug_smtp_password_field', __('SMTP Passord','skybug'), function(){
        $val = get_option('skybug_smtp_password','');
        echo '<input type="password" name="skybug_smtp_password" value="' . esc_attr($val) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__('E-post konto passord eller app-spesifikk passord.','skybug') . '</p>';
    }, 'skybug_settings_page','skybug_smtp_section');
    
    // SMTP Security
    register_setting('skybug_settings_group','skybug_smtp_security', array(
        'type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>'tls'
    ));
    add_settings_field('skybug_smtp_security_field', __('SMTP Sikkerhet','skybug'), function(){
        $val = get_option('skybug_smtp_security', 'tls');
        echo '<select name="skybug_smtp_security">';
        echo '<option value="none"' . selected($val, 'none', false) . '>' . esc_html__('Ingen','skybug') . '</option>';
        echo '<option value="tls"' . selected($val, 'tls', false) . '>' . esc_html__('TLS','skybug') . '</option>';
        echo '<option value="ssl"' . selected($val, 'ssl', false) . '>' . esc_html__('SSL','skybug') . '</option>';
        echo '</select>';
        echo '<p class="description">' . esc_html__('Anbefalt: TLS for port 587, SSL for port 465.','skybug') . '</p>';
    }, 'skybug_settings_page','skybug_smtp_section');
    
    // SMTP From Email
    register_setting('skybug_settings_group','skybug_smtp_from_email', array(
        'type'=>'string','sanitize_callback'=>'sanitize_email','default'=>''
    ));
    add_settings_field('skybug_smtp_from_email_field', __('Fra-adresse','skybug'), function(){
        $val = get_option('skybug_smtp_from_email','');
        echo '<input type="email" name="skybug_smtp_from_email" value="' . esc_attr($val) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__('E-postadresse som brukes som avsender.','skybug') . '</p>';
    }, 'skybug_settings_page','skybug_smtp_section');
    
    // SMTP From Name
    register_setting('skybug_settings_group','skybug_smtp_from_name', array(
        'type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>'SkyBug'
    ));
    add_settings_field('skybug_smtp_from_name_field', __('Fra-navn','skybug'), function(){
        $val = get_option('skybug_smtp_from_name', 'SkyBug');
        echo '<input type="text" name="skybug_smtp_from_name" value="' . esc_attr($val) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__('Visningsnavn for avsender.','skybug') . '</p>';
    }, 'skybug_settings_page','skybug_smtp_section');
    
    // IMAP seksjon
    add_settings_section('skybug_imap_section', __('IMAP Konfigurasjon','skybug'), function(){
        echo '<p>' . esc_html__('Konfigurer IMAP for å motta e-post og automatisk opprette saker.','skybug') . '</p>';
    }, 'skybug_settings_page');
    
    // IMAP Enable
    register_setting('skybug_settings_group','skybug_imap_enabled', array(
        'type'=>'boolean','sanitize_callback'=>'rest_sanitize_boolean','default'=>false
    ));
    add_settings_field('skybug_imap_enabled_field', __('Aktiver IMAP','skybug'), function(){
        $val = get_option('skybug_imap_enabled', false);
        echo '<input type="checkbox" name="skybug_imap_enabled" value="1" ' . checked($val, true, false) . ' />';
        echo '<p class="description">' . esc_html__('Aktiver automatisk mottak av e-post for å opprette saker.','skybug') . '</p>';
    }, 'skybug_settings_page','skybug_imap_section');
    
    // IMAP Host
    register_setting('skybug_settings_group','skybug_imap_host', array(
        'type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>''
    ));
    add_settings_field('skybug_imap_host_field', __('IMAP Server','skybug'), function(){
        $val = get_option('skybug_imap_host','');
        echo '<input type="text" name="skybug_imap_host" value="' . esc_attr($val) . '" class="regular-text" placeholder="imap.gmail.com" />';
        echo '<p class="description">' . esc_html__('IMAP server hostname eller IP-adresse.','skybug') . '</p>';
    }, 'skybug_settings_page','skybug_imap_section');
    
    // IMAP Port
    register_setting('skybug_settings_group','skybug_imap_port', array(
        'type'=>'integer','sanitize_callback'=>'absint','default'=>993
    ));
    add_settings_field('skybug_imap_port_field', __('IMAP Port','skybug'), function(){
        $val = get_option('skybug_imap_port', 993);
        echo '<input type="number" name="skybug_imap_port" value="' . esc_attr($val) . '" min="1" max="65535" />';
        echo '<p class="description">' . esc_html__('Standard: 993 (SSL) eller 143 (ingen kryptering).','skybug') . '</p>';
    }, 'skybug_settings_page','skybug_imap_section');
    
    // IMAP Username
    register_setting('skybug_settings_group','skybug_imap_username', array(
        'type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>''
    ));
    add_settings_field('skybug_imap_username_field', __('IMAP Brukernavn','skybug'), function(){
        $val = get_option('skybug_imap_username','');
        echo '<input type="text" name="skybug_imap_username" value="' . esc_attr($val) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__('E-postadresse eller brukernavn for IMAP-tilgang.','skybug') . '</p>';
    }, 'skybug_settings_page','skybug_imap_section');
    
    // IMAP Password
    register_setting('skybug_settings_group','skybug_imap_password', array(
        'type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>''
    ));
    add_settings_field('skybug_imap_password_field', __('IMAP Passord','skybug'), function(){
        $val = get_option('skybug_imap_password','');
        echo '<input type="password" name="skybug_imap_password" value="' . esc_attr($val) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__('E-post konto passord eller app-spesifikk passord.','skybug') . '</p>';
    }, 'skybug_settings_page','skybug_imap_section');
    
    // IMAP Security
    register_setting('skybug_settings_group','skybug_imap_security', array(
        'type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>'ssl'
    ));
    add_settings_field('skybug_imap_security_field', __('IMAP Sikkerhet','skybug'), function(){
        $val = get_option('skybug_imap_security', 'ssl');
        echo '<select name="skybug_imap_security">';
        echo '<option value="none"' . selected($val, 'none', false) . '>' . esc_html__('Ingen','skybug') . '</option>';
        echo '<option value="ssl"' . selected($val, 'ssl', false) . '>' . esc_html__('SSL','skybug') . '</option>';
        echo '<option value="tls"' . selected($val, 'tls', false) . '>' . esc_html__('TLS','skybug') . '</option>';
        echo '</select>';
        echo '<p class="description">' . esc_html__('Anbefalt: SSL for port 993.','skybug') . '</p>';
    }, 'skybug_settings_page','skybug_imap_section');
    
    // IMAP Folder
    register_setting('skybug_settings_group','skybug_imap_folder', array(
        'type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>'INBOX'
    ));
    add_settings_field('skybug_imap_folder_field', __('IMAP Mappe','skybug'), function(){
        $val = get_option('skybug_imap_folder', 'INBOX');
        echo '<input type="text" name="skybug_imap_folder" value="' . esc_attr($val) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__('Mappe å overvåke for innkommende saker (standard: INBOX).','skybug') . '</p>';
    }, 'skybug_settings_page','skybug_imap_section');
});
# slutt o6p7q8r9

// Utvid render_settings_page for å vise registerte settings
# p7q8r9s1 - Render Innstillinger utvidelse - se AI-learned/funksjonslogg.json
if(!function_exists('skybug_render_settings_page')){
    // fallback hvis ikke definert (skal være definert tidligere)
}
add_action('admin_menu', function(){
    // sikrer at settings form hook kjøres
});
# slutt p7q8r9s1

// Epostvarsel ved ny sak - nå med SMTP støtte
# q8r9s1t2 - Send epost ved ny offentliggjort sak med SMTP - se AI-learned/funksjonslogg.json
add_action('publish_skybug_issue', function($post_id, $post){
    // Bare ved opprettelse (ikke oppdatering fra annen status til publish unntatt auto)
    if (wp_is_post_revision($post_id)) { return; }
    $email = get_option('skybug_notify_email','');
    if(!$email) { return; }
    $subject = sprintf(__('Ny sak: %s','skybug'), $post->post_title);
    $program_id = get_post_meta($post_id, '_skybug_program_id', true);
    $program_name = $program_id ? get_the_title($program_id) : __('(Ukjent program)','skybug');
    $body = sprintf(__('En ny sak ble registrert for program "%1$s". Tittel: %2$s','skybug'), $program_name, $post->post_title) . "\n";
    $body .= admin_url('post.php?post='.$post_id.'&action=edit');
    
    // Bruk SMTP hvis konfigurert, ellers standard wp_mail
    skybug_send_email($email, $subject, $body);
},10,2);

// Ny funksjon for å sende e-post via SMTP eller wp_mail
function skybug_send_email($to, $subject, $body, $headers = array()) {
    $smtp_host = get_option('skybug_smtp_host', '');
    $smtp_username = get_option('skybug_smtp_username', '');
    
    // Hvis SMTP ikke er konfigurert, bruk standard wp_mail
    if (empty($smtp_host) || empty($smtp_username)) {
        return wp_mail($to, $subject, $body, $headers);
    }
    
    // Konfigurer PHPMailer for SMTP
    add_action('phpmailer_init', 'skybug_configure_phpmailer');
    
    // Sett fra-adresse og navn fra SMTP innstillinger
    add_filter('wp_mail_from', function($from) {
        $smtp_from = get_option('skybug_smtp_from_email', '');
        return !empty($smtp_from) ? $smtp_from : $from;
    });
    
    add_filter('wp_mail_from_name', function($name) {
        $smtp_name = get_option('skybug_smtp_from_name', 'SkyBug');
        return !empty($smtp_name) ? $smtp_name : $name;
    });
    
    $result = wp_mail($to, $subject, $body, $headers);
    
    // Fjern filters og actions etter bruk
    remove_action('phpmailer_init', 'skybug_configure_phpmailer');
    remove_all_filters('wp_mail_from');
    remove_all_filters('wp_mail_from_name');
    
    return $result;
}

// Konfigurer PHPMailer for SMTP
function skybug_configure_phpmailer($phpmailer) {
    $phpmailer->isSMTP();
    $phpmailer->Host = get_option('skybug_smtp_host', '');
    $phpmailer->Port = get_option('skybug_smtp_port', 587);
    $phpmailer->Username = get_option('skybug_smtp_username', '');
    $phpmailer->Password = get_option('skybug_smtp_password', '');
    $phpmailer->SMTPAuth = true;
    
    $security = get_option('skybug_smtp_security', 'tls');
    if ($security === 'ssl') {
        $phpmailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
    } elseif ($security === 'tls') {
        $phpmailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    }
    
    // Debug kun i development
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $phpmailer->SMTPDebug = 2;
        $phpmailer->Debugoutput = 'error_log';
    }
}

// AJAX handler for testing SMTP konfigurasjon
add_action('wp_ajax_skybug_test_smtp', 'skybug_test_smtp_connection');
function skybug_test_smtp_connection() {
    if (!current_user_can('manage_options')) {
        wp_send_json(array('success' => false, 'message' => 'Insufficient permissions'));
    }
    if (!check_ajax_referer('skybug_test_smtp', 'nonce', false)) {
        wp_send_json(array('success' => false, 'message' => 'Invalid nonce'));
    }
    $test_email = get_option('skybug_notify_email', '');
    if (empty($test_email)) {
        wp_send_json(array('success' => false, 'message' => 'Ingen varslings-epost er konfigurert'));
    }
    $subject = __('SkyBug SMTP Test', 'skybug');
    $body = __('Dette er en test-epost fra SkyBug SMTP konfigurasjon. Hvis du mottar denne meldingen, fungerer SMTP innstillingene korrekt.', 'skybug');
    $result = skybug_send_email($test_email, $subject, $body);
    if ($result) {
        error_log('[SkyBug][SMTP TEST] OK sendt til ' . $test_email);
        wp_send_json(array('success' => true, 'message' => 'Test e-post sendt til ' . $test_email));
    }
    error_log('[SkyBug][SMTP TEST] FEIL til ' . $test_email);
    wp_send_json(array('success' => false, 'message' => 'Feil ved sending av test e-post. Sjekk SMTP innstillinger.'));
}

// AJAX handler for testing IMAP forbindelse
add_action('wp_ajax_skybug_test_imap', 'skybug_test_imap_connection');
function skybug_test_imap_connection() {
    if (!current_user_can('manage_options')) {
        wp_send_json(array('success' => false, 'message' => 'Insufficient permissions'));
    }
    if (!check_ajax_referer('skybug_test_imap', 'nonce', false)) {
        wp_send_json(array('success' => false, 'message' => 'Invalid nonce'));
    }
    $host = get_option('skybug_imap_host', '');
    $port = get_option('skybug_imap_port', 993);
    $username = get_option('skybug_imap_username', '');
    $password = get_option('skybug_imap_password', '');
    $security = get_option('skybug_imap_security', 'ssl');
    if (empty($host) || empty($username) || empty($password)) {
        wp_send_json(array('success' => false, 'message' => 'IMAP innstillinger er ikke fullstendig konfigurert'));
    }
    $folder = get_option('skybug_imap_folder', 'INBOX');
    $attempts = skybug_attempt_imap_connections($host,$port,$security,$username,$password,$folder);
    foreach($attempts as $att){ if($att['success']){ $ok = $att; break; } }
    if(isset($ok)){
        $message = sprintf(__('IMAP forbindelse vellykket. Mappe "%s" har %d meldinger. (Strategi: %s)', 'skybug'), $ok['folder'], $ok['messages'], $ok['label']);
        error_log('[SkyBug][IMAP TEST] OK strategi=' . $ok['label'] . ' folder=' . $ok['folder'] . ' messages=' . $ok['messages']);
        wp_send_json(array('success'=>true,'message'=>$message,'attempts'=>$attempts));
    }
    $lastErr = end($attempts);
    $errMsg = isset($lastErr['error']) ? $lastErr['error'] : 'ukjent';
    error_log('[SkyBug][IMAP TEST] FEIL siste=' . $errMsg);
    wp_send_json(array('success'=>false,'message'=>'IMAP forbindelse feilet: '.$errMsg,'attempts'=>$attempts));
}
# slutt q8r9s1t2

// AJAX handler for manual IMAP email check - NYTT
add_action('wp_ajax_skybug_check_imap_emails', 'skybug_check_imap_emails');
function skybug_check_imap_emails() {
    if (!current_user_can('manage_options')) {
        wp_die(json_encode(array('success' => false, 'message' => 'Insufficient permissions')));
    }
    
    if (!check_ajax_referer('skybug_imap_check', 'nonce', false)) {
        wp_die(json_encode(array('success' => false, 'message' => 'Invalid nonce')));
    }
    
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    
    // Basic IMAP configuration check
    $imap_enabled = get_option('skybug_imap_enabled', false);
    $host = get_option('skybug_imap_host', '');
    $username = get_option('skybug_imap_username', '');
    $password = get_option('skybug_imap_password', '');
    
    if (!$imap_enabled || empty($host) || empty($username) || empty($password)) {
        wp_die(json_encode(array('success' => false, 'message' => 'IMAP ikke korrekt konfigurert')));
    }
    
    try {
        if (!function_exists('imap_open')) {
            wp_die(json_encode(array('success' => false, 'message' => 'IMAP PHP extension mangler på serveren')));
        }
        // Basic IMAP connection (simplified for demonstration)
        $port = get_option('skybug_imap_port', 993);
        $security = get_option('skybug_imap_security', 'ssl');
        $folder = get_option('skybug_imap_folder', 'INBOX');
        
        $connection_string = '{' . $host . ':' . $port;
        if ($security === 'ssl') {
            $connection_string .= '/imap/ssl';
        } elseif ($security === 'tls') {
            $connection_string .= '/imap/tls';
        }
        $connection_string .= '}' . $folder;
        
        $connection = @imap_open($connection_string, $username, $password);
        
        if (!$connection) {
            $error = imap_last_error();
            wp_die(json_encode(array('success' => false, 'message' => 'IMAP forbindelse feilet: ' . $error)));
        }
        
        // Get recent emails (last 10)
        $mailbox_info = imap_status($connection, $connection_string, SA_ALL);
        $total_emails = $mailbox_info->messages;
        
        $new_emails = 0;
        $emails_found = array();
        
        if ($total_emails > 0) {
            $recent_emails = imap_search($connection, 'UNSEEN', SE_UID);
            
            if ($recent_emails) {
                foreach (array_slice($recent_emails, 0, 5) as $email_uid) { // Process max 5 recent emails
                    $header = imap_headerinfo($connection, imap_msgno($connection, $email_uid));
                    $subject = isset($header->subject) ? $header->subject : 'No Subject';
                    $from_email = isset($header->from[0]) ? $header->from[0]->mailbox . '@' . $header->from[0]->host : 'Unknown';
                    $from_name = isset($header->from[0]->personal) ? $header->from[0]->personal : $from_email;
                    
                    $email_data = array(
                        'subject' => $subject,
                        'from' => $from_name . ' <' . $from_email . '>',
                        'date' => isset($header->date) ? strtotime($header->date) : time(),
                        'uid' => $email_uid
                    );
                    
                    $emails_found[] = $email_data;
                    $new_emails++;
                    
                    // Check if email might be related to this issue
                    if ($post_id && (strpos($subject, '#' . $post_id) !== false || strpos($subject, 'sak ' . $post_id) !== false)) {
                        // Store as related email
                        $existing_emails = get_post_meta($post_id, '_skybug_related_emails', true) ?: array();
                        $existing_emails[] = $email_data;
                        update_post_meta($post_id, '_skybug_related_emails', $existing_emails);
                    }
                }
            }
        }
        
        imap_close($connection);
        
        // Update last check time
        update_option('skybug_last_imap_check', current_time('timestamp'));
        
        $message = sprintf(__('Sjekket %d e-poster. %d nye funnet.', 'skybug'), min($total_emails, 5), $new_emails);
        wp_die(json_encode(array('success' => true, 'message' => $message, 'emails' => $emails_found)));
        
    } catch (Exception $e) {
        wp_die(json_encode(array('success' => false, 'message' => 'Feil under e-post henting: ' . $e->getMessage())));
    }
}

// Registrer admin meny
add_action('admin_menu', 'skybug_register_admin_menu');

// Enqueue moderne CSS og JavaScript
add_action('admin_enqueue_scripts', 'skybug_enqueue_modern_assets');

// Bulk delete handler for selected bugs
add_action('wp_ajax_skybug_bulk_delete', function(){
    if(!current_user_can('delete_posts')){
        wp_send_json_error(['message'=>'perm']);
    }
    if(!check_ajax_referer('skybug_bulk_delete','nonce',false)){
        wp_send_json_error(['message'=>'nonce']);
    }
    $ids = isset($_POST['ids']) ? (array) $_POST['ids'] : [];
    $deleted = [];
    foreach($ids as $id){
        $pid = intval($id);
        if($pid && get_post_type($pid)==='skybug_issue'){
            $res = wp_delete_post($pid,true);
            if($res){ $deleted[] = $pid; }
        }
    }
    wp_send_json_success(['deleted'=>$deleted]);
});

// Helper function to extract readable snippet from email body (handling MIME multipart)
function skybug_extract_email_snippet($imap_connection, $msgno, $max_length = 200) {
    $body = @imap_body($imap_connection, $msgno, FT_PEEK);
    if (!$body) return '';
    
    // Log raw body for debugging
    $log_entry = date('c') . " SNIPPET_DEBUG msgno={$msgno} body_length=" . strlen($body) . " starts_with=" . substr($body, 0, 100) . "\n";
    @file_put_contents(WP_CONTENT_DIR . '/skybug-imap.log', $log_entry, FILE_APPEND);
    
    // Check message structure first
    $structure = @imap_fetchstructure($imap_connection, $msgno);
    if ($structure) {
        // Single part message
        if (!isset($structure->parts)) {
            $decoded = skybug_decode_single_part($body, $structure);
            if ($decoded) {
                $result = skybug_clean_snippet($decoded, $max_length);
                $log_entry = date('c') . " SNIPPET_DEBUG single_part result_length=" . strlen($result) . " result=" . substr($result, 0, 50) . "\n";
                @file_put_contents(WP_CONTENT_DIR . '/skybug-imap.log', $log_entry, FILE_APPEND);
                return $result;
            }
        }
        // Multi-part message
        else {
            foreach ($structure->parts as $partNum => $part) {
                $partBody = @imap_fetchbody($imap_connection, $msgno, $partNum + 1, FT_PEEK);
                if ($partBody && isset($part->subtype)) {
                    $log_entry = date('c') . " SNIPPET_DEBUG part=" . ($partNum + 1) . " subtype=" . $part->subtype . " body_length=" . strlen($partBody) . "\n";
                    @file_put_contents(WP_CONTENT_DIR . '/skybug-imap.log', $log_entry, FILE_APPEND);
                    
                    // Prefer plain text
                    if (strtoupper($part->subtype) === 'PLAIN') {
                        $decoded = skybug_decode_mime_part($partBody, $part);
                        if ($decoded && trim($decoded)) {
                            $result = skybug_clean_snippet($decoded, $max_length);
                            $log_entry = date('c') . " SNIPPET_DEBUG plain_part result=" . substr($result, 0, 50) . "\n";
                            @file_put_contents(WP_CONTENT_DIR . '/skybug-imap.log', $log_entry, FILE_APPEND);
                            return $result;
                        }
                    }
                }
            }
            // Try HTML parts
            foreach ($structure->parts as $partNum => $part) {
                $partBody = @imap_fetchbody($imap_connection, $msgno, $partNum + 1, FT_PEEK);
                if ($partBody && isset($part->subtype) && strtoupper($part->subtype) === 'HTML') {
                    $decoded = skybug_decode_mime_part($partBody, $part);
                    if ($decoded && trim(strip_tags($decoded))) {
                        $result = skybug_clean_snippet(strip_tags($decoded), $max_length);
                        $log_entry = date('c') . " SNIPPET_DEBUG html_part result=" . substr($result, 0, 50) . "\n";
                        @file_put_contents(WP_CONTENT_DIR . '/skybug-imap.log', $log_entry, FILE_APPEND);
                        return $result;
                    }
                }
            }
        }
    }
    
    // Last resort: aggressive fallback parsing
    $fallback = skybug_aggressive_text_extract($body);
    $result = skybug_clean_snippet($fallback, $max_length);
    $log_entry = date('c') . " SNIPPET_DEBUG fallback result=" . substr($result, 0, 50) . "\n";
    @file_put_contents(WP_CONTENT_DIR . '/skybug-imap.log', $log_entry, FILE_APPEND);
    return $result;
}

// Helper to decode single-part message
function skybug_decode_single_part($body, $structure) {
    if (isset($structure->encoding)) {
        switch ($structure->encoding) {
            case 3: // BASE64
                return base64_decode($body);
            case 4: // QUOTED-PRINTABLE
                return quoted_printable_decode($body);
        }
    }
    return $body;
}

// Aggressive text extraction for complex MIME messages
function skybug_aggressive_text_extract($body) {
    // Remove MIME headers and boundaries
    $text = preg_replace('/^.*?boundary="[^"]*"/mi', '', $body);
    $text = preg_replace('/------=_[A-F0-9-]+[^\r\n]*/mi', '', $text);
    $text = preg_replace('/Content-Type:[^\r\n]*\r?\n/mi', '', $text);
    $text = preg_replace('/Content-Transfer-Encoding:[^\r\n]*\r?\n/mi', '', $text);
    $text = preg_replace('/Content-Disposition:[^\r\n]*\r?\n/mi', '', $text);
    
    // Split into lines and find actual content
    $lines = preg_split('/[\r\n]+/', $text);
    $contentLines = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip empty lines, headers, and boundary-like content
        if (empty($line)) continue;
        if (preg_match('/^(Content-|MIME-|Message-|Date:|From:|To:|Subject:)/i', $line)) continue;
        if (preg_match('/^[-=_]{3,}/', $line)) continue;
        if (preg_match('/^[A-Za-z0-9+\/]{40,}={0,2}$/', $line)) {
            // Might be base64 - try to decode
            $decoded = base64_decode($line, true);
            if ($decoded !== false && ctype_print(substr($decoded, 0, 10))) {
                $contentLines[] = $decoded;
            }
            continue;
        }
        
        // This looks like actual content
        if (strlen($line) > 5 && !preg_match('/^[^a-zA-Z]*$/', $line)) {
            $contentLines[] = $line;
        }
    }
    
    $result = implode(' ', $contentLines);
    
    // Try to decode common encodings in the remaining text
    if (strpos($result, '=?') !== false) {
        // MIME encoded words
        $result = preg_replace_callback('/=\?[^?]+\?[BQ]\?[^?]*\?=/', function($matches) {
            $decoded = @imap_mime_header_decode($matches[0]);
            return $decoded[0]->text ?? $matches[0];
        }, $result);
    }
    
    // Look for quoted-printable patterns
    if (strpos($result, '=') !== false && preg_match('/=[0-9A-F]{2}/', $result)) {
        $result = quoted_printable_decode($result);
    }
    
    return $result;
}

// Helper to decode MIME part based on encoding
function skybug_decode_mime_part($body, $part) {
    // Handle encoding
    if (isset($part->encoding)) {
        switch ($part->encoding) {
            case 3: // BASE64
                $body = base64_decode($body);
                break;
            case 4: // QUOTED-PRINTABLE  
                $body = quoted_printable_decode($body);
                break;
        }
    }
    return $body;
}

// Helper to clean and truncate snippet
function skybug_clean_snippet($text, $max_length = 200) {
    // Remove MIME boundaries and headers first
    $text = preg_replace('/------=_MB[A-F0-9-]+/i', '', $text);
    $text = preg_replace('/------=_[A-F0-9-]+/i', '', $text);
    $text = preg_replace('/Content-Type:[^\r\n]*[\r\n]*/i', '', $text);
    $text = preg_replace('/Content-Transfer-Encoding:[^\r\n]*[\r\n]*/i', '', $text);
    $text = preg_replace('/Content-Disposition:[^\r\n]*[\r\n]*/i', '', $text);
    
    // Remove excessive whitespace and newlines
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    
    // Remove leading dashes or boundaries that might remain
    $text = preg_replace('/^[-=_\s]+/', '', $text);
    
    // If text is still empty or just noise, return empty
    if (strlen(trim($text)) < 3 || preg_match('/^[-=_\s]*$/', $text)) {
        return '';
    }
    
    // Truncate if needed
    if (strlen($text) > $max_length) {
        $text = substr($text, 0, $max_length - 3) . '...';
    }
    
    return $text;
}

// Fetch recent IMAP email subjects (last 5) for bugs overview box
add_action('wp_ajax_skybug_fetch_recent_imap_emails', function(){
    if(!current_user_can('manage_options')){ wp_send_json_error(['message'=>'perm']); }
    if(!check_ajax_referer('skybug_recent_emails','nonce',false)){ wp_send_json_error(['message'=>'nonce']); }
    $imap_enabled = get_option('skybug_imap_enabled', false);
    $host = get_option('skybug_imap_host','');
    $username = get_option('skybug_imap_username','');
    $password = get_option('skybug_imap_password','');
    $port = get_option('skybug_imap_port', 993);
    $security = get_option('skybug_imap_security','ssl');
    $folder = get_option('skybug_imap_folder','INBOX');
    if(!$imap_enabled || empty($host) || empty($username) || empty($password)){
        wp_send_json_error(['message'=>'not_configured']);
    }
    if(!function_exists('imap_open')){ wp_send_json_error(['message'=>'no_imap_ext']); }
    // Prefer previously successful strategy if stored
    $preferred = get_option('skybug_imap_preferred_strategy'); // array: ['base'=>..., 'folder'=>...]
    $attempts = [];
    if(is_array($preferred) && isset($preferred['base'],$preferred['folder'])){
        $tryString = $preferred['base'] . $preferred['folder'];
        $res = @imap_open($tryString,$username,$password);
        if($res){
            $attempts[] = [
                'label'=>'preferred',
                'success'=>true,
                'resource'=>$res,
                'base'=>$preferred['base'],
                'folder'=>$preferred['folder'],
                'messages'=>@imap_num_msg($res)
            ];
        } else {
            $attempts[] = [ 'label'=>'preferred','success'=>false,'error'=>imap_last_error(),'string'=>$tryString ];
        }
    }
    if(empty($attempts) || !$attempts[0]['success']){
        $attempts = array_merge($attempts, skybug_attempt_imap_connections($host,$port,$security,$username,$password,$folder,true));
    }
    // Velg success forsøk med flest meldinger (prioriter reell innhold)
    $selected = null; $maxMessages = -1;
    foreach($attempts as $att){
        if(!empty($att['success'])){
            $msgs = isset($att['messages']) ? intval($att['messages']) : 0;
            if($msgs > $maxMessages){ $maxMessages = $msgs; $selected = $att; }
        }
    }
    if(!$selected){ wp_send_json_error(['message'=>'connect_fail','attempts'=>$attempts]); }
    $inbox = $selected['resource'];
    $base = $selected['base'];
    $folder = $selected['folder'];
    $num = $selected['messages'];
    // Cache preferred strategy when not already cached or changed
    if($selected['label'] !== 'preferred' && $num > 0){
        update_option('skybug_imap_preferred_strategy', ['base'=>$base,'folder'=>$folder], false);
    }
    $subjects = [];
    $log_parts = ['initial_total='.$num];
    if($num === 0){
        $alt_flags = [];
        if(strpos($base,'/novalidate-cert') === false){ $alt_flags[] = '/novalidate-cert'; }
        $folder_alts = [];
        if(strtoupper($folder) !== 'INBOX'){ $folder_alts[] = 'INBOX'; }
        if($folder !== 'INBOX' && strpos($folder,'INBOX') !== 0){ $folder_alts[] = 'INBOX.'.$folder; }
        $recovered = false;
        foreach($alt_flags as $flag){
            if($recovered) break;
            $testBase = '{' . $host . ':' . $port;
            if($security==='ssl'){ $testBase .= '/imap/ssl'; }
            elseif($security==='tls'){ $testBase .= '/imap/tls'; }
            $testBase .= $flag . '}';
            $targets = $folder_alts ? $folder_alts : [$folder];
            foreach($targets as $tf){
                $tryString = $testBase.$tf;
                $altInbox = @imap_open($tryString, $username, $password);
                if($altInbox){
                    $newNum = imap_num_msg($altInbox);
                    $log_parts[] = 'alt_try='.$tryString.' newTotal='.$newNum;
                    if($newNum > 0){
                        imap_close($inbox);
                        $inbox = $altInbox; $num = $newNum; $recovered = true; break;
                    }
                    imap_close($altInbox);
                } else { $log_parts[] = 'alt_fail='.$tryString; }
            }
        }
        if(!$recovered){ $log_parts[] = 'recovery_failed=1'; }
    }
    if($num > 0){
        $start = max(1, $num - 9); // fetch up to last 10
        $sequence = $start.':'.$num;
        $overview = @imap_fetch_overview($inbox, $sequence, 0);
        if($overview && is_array($overview)){
            usort($overview, function($a,$b){ return $b->msgno <=> $a->msgno; });
            foreach($overview as $ov){
                if(count($subjects) >= 5) break;
                $rawSubject = isset($ov->subject)? $ov->subject : '(ingen emne)';
                $decoded=''; $parts = @imap_mime_header_decode($rawSubject); if($parts){ foreach($parts as $p){ $decoded.=$p->text; } }
                $uid = isset($ov->uid)? $ov->uid : (isset($ov->msgno)? $ov->msgno : null);
                // Intelligent snippet extraction handling MIME multipart
                $snippet = '';
                if(isset($ov->msgno)){
                    $snippet = skybug_extract_email_snippet($inbox, $ov->msgno);
                }
                // From header
                $fromRaw = isset($ov->from)? $ov->from : '';
                $fromDecoded=''; if($fromRaw){ $fparts = @imap_mime_header_decode($fromRaw); if($fparts){ foreach($fparts as $fp){ $fromDecoded.=$fp->text; } } }
                $subjects[] = [ 'subject'=> trim($decoded) !== '' ? $decoded : '(ingen emne)', 'date'=> isset($ov->date)? $ov->date : '', 'uid'=>$uid, 'from'=>$fromDecoded, 'snippet'=>$snippet ];
            }
        }
    }
    if(empty($subjects) && $num > 0){
        $search = @imap_search($inbox,'ALL', SE_UID);
        if($search){
            rsort($search);
            foreach(array_slice($search,0,5) as $uid){
                $ovArr = @imap_fetch_overview($inbox,$uid,FT_UID);
                if(!$ovArr || !isset($ovArr[0])) continue;
                $ov = $ovArr[0];
                $rawSubject = isset($ov->subject)? $ov->subject : '(ingen emne)';
                $decoded=''; $parts = @imap_mime_header_decode($rawSubject); if($parts){ foreach($parts as $p){ $decoded.=$p->text; } }
                $subjects[] = [ 'subject'=> trim($decoded) !== '' ? $decoded : '(ingen emne)', 'date'=> isset($ov->date)? $ov->date : '' ];
            }
            $log_parts[]='fallback_search_used=1';
        }
    }
    imap_close($inbox);
    $log_parts[] = 'strategy='.($selected['label'] ?? 'primary');
    $log_line = date('c')." RECENT_EMAILS_DEBUG ".implode(' ',$log_parts).' returned='.count($subjects)."\n";
    @file_put_contents(WP_CONTENT_DIR.'/skybug-imap.log', $log_line, FILE_APPEND);
    // Close handled inside helper only on failure paths; close now for success
    if(is_resource($inbox)){ imap_close($inbox); }
    wp_send_json_success(['emails'=>$subjects,'total'=>$num,'attempts'=>$attempts]);
});

// Konverter IMAP e-post til sak
add_action('wp_ajax_skybug_convert_imap_email', function(){
    if(!current_user_can('edit_posts')){ wp_send_json_error(['message'=>'perm']); }
    if(!check_ajax_referer('skybug_imap_convert','nonce',false)){ wp_send_json_error(['message'=>'nonce']); }
    $uid = isset($_POST['uid']) ? sanitize_text_field($_POST['uid']) : '';
    $subject = sanitize_text_field($_POST['subject'] ?? '');
    $snippet = sanitize_textarea_field($_POST['snippet'] ?? '');
    $from = sanitize_text_field($_POST['from'] ?? '');
    $type = ($_POST['issue_type'] ?? 'bug') === 'feature' ? 'feature' : 'bug';
    if(!$uid || !$subject){ wp_send_json_error(['message'=>'missing']); }
    // Duplikatvern
    $dupe = get_posts(['post_type'=>'skybug_issue','meta_key'=>'_skybug_imap_uid','meta_value'=>$uid,'fields'=>'ids','posts_per_page'=>1]);
    if($dupe){ wp_send_json_error(['message'=>'exists','post_id'=>$dupe[0]]); }
    $content = $snippet ? $snippet : __('(Ingen forhåndsvisning tilgjengelig)','skybug');
    $post_id = wp_insert_post([
        'post_type'=>'skybug_issue',
        'post_status'=>'publish',
        'post_title'=> $subject,
        'post_content'=> $content . ( $from ? "\n\nFra: $from" : '' ),
    ], true);
    if(is_wp_error($post_id)){ wp_send_json_error(['message'=>'insert_fail']); }
    update_post_meta($post_id,'_skybug_imap_uid',$uid);
    update_post_meta($post_id,'_skybug_origin','imap');
    if($type === 'feature'){
        wp_set_object_terms($post_id,'feature','skybug_type', false);
    } else {
        wp_set_object_terms($post_id,'bug','skybug_type', false);
    }
    wp_send_json_success(['post_id'=>$post_id,'edit_link'=>get_edit_post_link($post_id, 'raw')]);
});

// Marker/slett IMAP e-post (best effort – kun flagg, faktisk slett krever expunge senere)
add_action('wp_ajax_skybug_delete_imap_email', function(){
    if(!current_user_can('manage_options')){ wp_send_json_error(['message'=>'perm']); }
    if(!check_ajax_referer('skybug_imap_delete','nonce',false)){ wp_send_json_error(['message'=>'nonce']); }
    $uid = isset($_POST['uid']) ? sanitize_text_field($_POST['uid']) : '';
    if(!$uid){ wp_send_json_error(['message'=>'missing']); }
    $host = get_option('skybug_imap_host','');
    $username = get_option('skybug_imap_username','');
    $password = get_option('skybug_imap_password','');
    $port = get_option('skybug_imap_port', 993);
    $security = get_option('skybug_imap_security','ssl');
    $folder = get_option('skybug_imap_folder','INBOX');
    if(empty($host) || empty($username) || empty($password)){ wp_send_json_error(['message'=>'not_configured']); }
    if(!function_exists('imap_open')){ wp_send_json_error(['message'=>'no_ext']); }
    $attempts = skybug_attempt_imap_connections($host,$port,$security,$username,$password,$folder,true);
    $conn = null; foreach($attempts as $a){ if(!empty($a['success'])){ $conn = $a['resource']; $folder=$a['folder']; break; } }
    if(!$conn){ wp_send_json_error(['message'=>'connect_fail']); }
    // Finn msgno fra UID
    $search = @imap_search($conn,'UID '.$uid, SE_UID);
    if(!$search){ imap_close($conn); wp_send_json_error(['message'=>'not_found']); }
    $msgno = imap_msgno($conn, $uid);
    if($msgno){ @imap_delete($conn, $msgno); }
    imap_close($conn, CL_EXPUNGE);
    wp_send_json_success(['deleted'=>true,'uid'=>$uid]);
});

// AJAX endpoint for statistics chart data
add_action('wp_ajax_skybug_get_statistics_data', function(){
    if(!current_user_can('manage_options')){ wp_send_json_error(['message'=>'perm']); }
    
    $period = isset($_POST['period']) ? sanitize_text_field($_POST['period']) : '30d';
    $chart_type = isset($_POST['chart_type']) ? sanitize_text_field($_POST['chart_type']) : 'main';
    
    $data = array();
    
    if ($chart_type === 'main') {
        // Get monthly trend data for last 6 months
        $months = array();
        $bug_data = array();
        $feature_data = array();
        
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-{$i} months"));
            $month_start = $month . '-01';
            $month_end = date('Y-m-t', strtotime($month_start));
            
            // Count bugs created in this month
            $bugs = get_posts(array(
                'post_type' => 'skybug_issue',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'date_query' => array(
                    array(
                        'after' => $month_start,
                        'before' => $month_end,
                        'inclusive' => true
                    )
                ),
                'tax_query' => array(array('taxonomy' => 'skybug_type', 'field' => 'slug', 'terms' => 'bug'))
            ));
            
            // Count features created in this month
            $features = get_posts(array(
                'post_type' => 'skybug_issue',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'date_query' => array(
                    array(
                        'after' => $month_start,
                        'before' => $month_end,
                        'inclusive' => true
                    )
                ),
                'tax_query' => array(array('taxonomy' => 'skybug_type', 'field' => 'slug', 'terms' => 'feature'))
            ));
            
            $months[] = date('M', strtotime($month_start));
            $bug_data[] = count($bugs);
            $feature_data[] = count($features);
        }
        
        $data = array(
            'labels' => $months,
            'datasets' => array(
                array(
                    'label' => 'Bugs',
                    'data' => $bug_data,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.8)'
                ),
                array(
                    'label' => 'Features',
                    'data' => $feature_data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)'
                )
            )
        );
    }
    elseif ($chart_type === 'distribution') {
        // Get status distribution instead of priority (more meaningful with few issues)
        $statuses = array(
            'publish' => array('count' => 0, 'label' => 'Nye'),
            'skybug_in_progress' => array('count' => 0, 'label' => 'Under arbeid'),
            'skybug_waiting' => array('count' => 0, 'label' => 'Venter'),
            'skybug_resolved' => array('count' => 0, 'label' => 'Løst'),
            'skybug_closed' => array('count' => 0, 'label' => 'Lukket')
        );
        
        $issues = get_posts(array(
            'post_type' => 'skybug_issue',
            'posts_per_page' => -1,
            'post_status' => array_keys($statuses)
        ));
        
        foreach ($issues as $issue) {
            if (isset($statuses[$issue->post_status])) {
                $statuses[$issue->post_status]['count']++;
            }
        }
        
        // Only include statuses with count > 0
        $filtered_labels = array();
        $filtered_data = array();
        $filtered_colors = array(
            'publish' => 'rgb(59, 130, 246)',
            'skybug_in_progress' => 'rgb(245, 158, 11)',
            'skybug_waiting' => 'rgb(168, 85, 247)',
            'skybug_resolved' => 'rgb(16, 185, 129)',
            'skybug_closed' => 'rgb(107, 114, 128)'
        );
        $colors = array();
        
        foreach ($statuses as $status => $info) {
            if ($info['count'] > 0) {
                $filtered_labels[] = $info['label'];
                $filtered_data[] = $info['count'];
                $colors[] = $filtered_colors[$status];
            }
        }
        
        $data = array(
            'labels' => $filtered_labels,
            'datasets' => array(array(
                'data' => $filtered_data,
                'backgroundColor' => $colors
            ))
        );
    }
    elseif ($chart_type === 'trend') {
        // Get daily activity for specified period
        $days = ($period === '30d') ? 30 : (($period === '90d') ? 90 : 365);
        $labels = array();
        $activity_data = array();
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $date_start = $date . ' 00:00:00';
            $date_end = $date . ' 23:59:59';
            
            $daily_count = get_posts(array(
                'post_type' => 'skybug_issue',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'date_query' => array(
                    array(
                        'after' => $date_start,
                        'before' => $date_end,
                        'inclusive' => true
                    )
                )
            ));
            
            $labels[] = date('j', strtotime($date));
            $activity_data[] = count($daily_count);
        }
        
        $data = array(
            'labels' => $labels,
            'datasets' => array(array(
                'label' => 'Daily Activity',
                'data' => $activity_data,
                'borderColor' => 'rgb(59, 130, 246)',
                'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                'tension' => 0.4,
                'fill' => true
            ))
        );
    }
    
    wp_send_json_success($data);
});

// Helper: attempt multiple IMAP connection variants
if(!function_exists('skybug_attempt_imap_connections')){
function skybug_attempt_imap_connections($host,$port,$security,$username,$password,$folder,$returnResource=false){
    $attempts = [];
    // Kortere timeouts for å unngå heng ved døde porter / nett
    if(function_exists('imap_timeout')){
        @imap_timeout(IMAP_OPENTIMEOUT, 5);
        @imap_timeout(IMAP_READTIMEOUT, 5);
        @imap_timeout(IMAP_WRITETIMEOUT, 5);
    }
    // Candidate base flag combos (ordered by likely success based on chosen security)
    $raw = ['','/imap'];
    if($security==='ssl'){ array_unshift($raw, '/imap/ssl', '/ssl'); }
    elseif($security==='tls'){ array_unshift($raw, '/imap/tls', '/tls'); }
    else { $raw[] = '/imap/notls'; }
    // Ensure uniqueness while preserving order
    $seen = [];$baseList=[];foreach($raw as $f){ if(!isset($seen[$f])){ $seen[$f]=1; $baseList[]=$f; } }
    // Append novalidate-cert versions
    $flagVariants=[];foreach($baseList as $f){ $flagVariants[]=$f; $flagVariants[]=$f.'/novalidate-cert'; }
    // Remove accidental duplicates
    $seen=[];$tmp=[];foreach($flagVariants as $f){ if(!isset($seen[$f])){ $seen[$f]=1; $tmp[]=$f;} } $flagVariants=$tmp;
    // Potential folder variants
    $folders = array_unique(array_filter([
        $folder,
        strtoupper($folder)==='INBOX' ? 'INBOX' : null,
        (strpos($folder,'INBOX.')===0 ? null : 'INBOX.'.$folder)
    ]));
    $resourceCaptured = false;
    foreach($flagVariants as $flags){
        foreach($folders as $f){
            $base = '{'.$host.':'.$port.$flags.'}'.$f;
            $res = @imap_open($base,$username,$password);
            $entry = [
                'label'=>$flags.'|'.$f,
                'base'=>'{'.$host.':'.$port.$flags.'}',
                'folder'=>$f,
                'success'=>false
            ];
            if($res){
                $entry['success']=true;
                $entry['messages']=imap_num_msg($res);
                if($returnResource && !$resourceCaptured){
                    $entry['resource']=$res; $resourceCaptured = true;
                } else {
                    imap_close($res);
                }
                $attempts[] = $entry;
                // Bryt tidlig – vi trenger ikke flere suksessforsøk for test
                break 2;
            } else {
                $entry['error']=imap_last_error();
                $attempts[]=$entry;
            }
        }
    }
    return $attempts;
}}

function skybug_enqueue_modern_assets($hook) {
    // Kun last på SkyBug sider
    if (strpos($hook, 'skybug') === false && $hook !== 'toplevel_page_skybug_dashboard') {
        return;
    }
    
    // Google Fonts - Inter
    wp_enqueue_style('skybug-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', array(), null);
    
    // Vårt moderne CSS framework
    wp_enqueue_style('skybug-framework-tokens', SKYBUG_URL . 'assets/css/framework/tokens.css', array(), SKYBUG_VERSION);
    wp_enqueue_style('skybug-framework-layout', SKYBUG_URL . 'assets/css/framework/layout.css', array('skybug-framework-tokens'), SKYBUG_VERSION);
    wp_enqueue_style('skybug-framework-components', SKYBUG_URL . 'assets/css/framework/components.css', array('skybug-framework-layout'), SKYBUG_VERSION);
    wp_enqueue_style('skybug-framework-notifications', SKYBUG_URL . 'assets/css/framework/notifications.css', array('skybug-framework-components'), SKYBUG_VERSION);
    
    // Side-spesifikke styles
    if ($hook === 'toplevel_page_skybug_dashboard') {
        wp_enqueue_style('skybug-dashboard', SKYBUG_URL . 'assets/css/pages/dashboard.css', array('skybug-framework-notifications'), SKYBUG_VERSION);
    }
    
    if (strpos($hook, 'skybug_settings') !== false) {
        wp_enqueue_style('skybug-dashboard', SKYBUG_URL . 'assets/css/pages/dashboard.css', array('skybug-framework-notifications'), SKYBUG_VERSION);
    }
    
    if (strpos($hook, 'skybug_program') !== false || (isset($_GET['post_type']) && $_GET['post_type'] === 'skybug_program') || 
        strpos($hook, 'skybug_bugs') !== false || strpos($hook, 'skybug_features') !== false) {
        wp_enqueue_style('skybug-programs', SKYBUG_URL . 'assets/css/pages/programs.css', array('skybug-framework-notifications'), SKYBUG_VERSION);
    }
    
    if (strpos($hook, 'skybug_stats') !== false) {
        wp_enqueue_style('skybug-statistics', SKYBUG_URL . 'assets/css/pages/statistics.css', array('skybug-framework-notifications'), SKYBUG_VERSION);
    }
    
    // Bestem dependencies for hovedscript
    $modern_deps = array('jquery');
    if (strpos($hook, 'skybug_stats') !== false || $hook === 'toplevel_page_skybug_dashboard') {
        // Chart.js kun nødvendig på dashboard og statistikk
        wp_enqueue_script('skybug-chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js', array(), '3.9.1', true);
        $modern_deps[] = 'skybug-chartjs';
    }

    $is_settings = (strpos($hook, 'skybug_settings') !== false);
    if($is_settings){
        // Lettvekts script for settings-side: bare test-knapper
        wp_enqueue_script('skybug-settings-lite', SKYBUG_URL . 'assets/js/settings-lite.js', array('jquery'), SKYBUG_VERSION, true);
        wp_localize_script('skybug-settings-lite','skyBugSettings', array(
            'ajaxUrl'=>admin_url('admin-ajax.php'),
            'nonces'=>array(
                'smtp'=>wp_create_nonce('skybug_test_smtp'),
                'imap'=>wp_create_nonce('skybug_test_imap')
            ),
            'strings'=>array(
                'testing'=>__('Tester...','skybug')
            )
        ));
    } else {
        // Moderne JavaScript framework for andre sider
        wp_enqueue_script('skybug-modern-gui', SKYBUG_URL . 'assets/js/modern-gui.js', $modern_deps, SKYBUG_VERSION, true);
    }

    // Ny moderne kort-basert UI for Bugs-side
    if (strpos($hook, 'skybug_bugs') !== false) {
        wp_enqueue_style('skybug-bugs-modern', SKYBUG_URL . 'assets/css/bugs-admin.css', array('skybug-framework-components'), SKYBUG_VERSION);
        wp_enqueue_script('skybug-bugs-modern', SKYBUG_URL . 'assets/js/bugs-admin.js', array(), SKYBUG_VERSION, true);
        wp_localize_script('skybug-bugs-modern', 'skybugBugs', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'emptyLabel' => __('Ingen saker matcher filter', 'skybug'),
            'nonces' => array(
                'imap' => wp_create_nonce('skybug_imap_check'),
                'bulkDelete' => wp_create_nonce('skybug_bulk_delete'),
                'recentEmails' => wp_create_nonce('skybug_recent_emails')
                , 'imapConvert' => wp_create_nonce('skybug_imap_convert')
                , 'imapDelete' => wp_create_nonce('skybug_imap_delete')
            ),
            'i18n' => array(
                'deleting' => __('Sletter...','skybug'),
                'deleteConfirm' => __('Slette valgte saker? Dette kan ikke angres.','skybug'),
                'imapError' => __('IMAP henting feilet','skybug'),
                'imapFetching' => __('Henter...','skybug'),
                'recentEmailsTitle' => __('Siste 5 E-poster','skybug'),
                'recentEmailsEmpty' => __('Ingen e-poster funnet','skybug'),
                'recentEmailsRefresh' => __('Oppdater','skybug')
            )
        ));
    }
    
    // Ticket management for issue admin list and custom bug/feature pages
    $screen = get_current_screen();
    if (($screen && $screen->id === 'edit-skybug_issue') || 
        strpos($hook, 'skybug_bugs') !== false || 
        strpos($hook, 'skybug_features') !== false) {
        wp_enqueue_style('skybug-ticket-management', SKYBUG_URL . 'assets/css/pages/ticket-management.css', array('skybug-framework-notifications'), SKYBUG_VERSION);
        wp_enqueue_script('skybug-ticket-management', SKYBUG_URL . 'assets/js/ticket-management.js', array('jquery'), SKYBUG_VERSION, true);
        wp_localize_script('skybug-ticket-management', 'skyBugTicket', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('skybug_quick_actions'),
            'strings' => array(
                'confirm' => __('Er du sikker?', 'skybug'),
                'error' => __('En feil oppstod', 'skybug'),
                'success' => __('Handling utført', 'skybug')
            )
        ));
    }
    
    // Issue edit page specific scripts
    if ($screen && $screen->id === 'skybug_issue') {
        wp_enqueue_script('skybug-issue-edit', SKYBUG_URL . 'assets/js/issue-edit.js', array('jquery'), SKYBUG_VERSION, true);
        wp_localize_script('skybug-issue-edit', 'skyBugIssue', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('skybug_send_email'),
            'imapNonce' => wp_create_nonce('skybug_imap_check'), // NYTT
            'strings' => array(
                'sending' => __('Sender...', 'skybug'),
                'sent' => __('E-post sendt!', 'skybug'),
                'error' => __('Feil ved sending', 'skybug'),
                'required' => __('Alle feltene er påkrevd', 'skybug')
            )
        ));
    }
    
    // Localization for JavaScript
    wp_localize_script('skybug-modern-gui', 'skyBugConfig', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'restUrl' => rest_url('skybug/v1/'),
        'nonce' => wp_create_nonce('wp_rest'),
        'strings' => array(
            'loading' => __('Loading...', 'skybug'),
            'error' => __('Error occurred', 'skybug'),
            'success' => __('Success!', 'skybug'),
            'confirm' => __('Are you sure?', 'skybug'),
            'close' => __('Close', 'skybug'),
            'save' => __('Save', 'skybug'),
            'cancel' => __('Cancel', 'skybug'),
        )
    ));
    
    if(!$is_settings){
        // AJAX nonces for test functions (separate for SMTP og IMAP) brukt av modern-gui
        wp_localize_script('skybug-modern-gui', 'wpAjax', array(
            'smtp' => wp_create_nonce('skybug_test_smtp'),
            'imap' => wp_create_nonce('skybug_test_imap')
        ));
    }
}

# 2b3c4d5e - Registrer adminmeny - se AI-learned/funksjonslogg.json
function skybug_register_admin_menu() {
    // Hovedmeny med posisjon 1 for høyeste prioritet
    add_menu_page(
        __('SkyBug Dashboard','skybug'),   // sidetittel
        __('SkyBug','skybug'),            // menytittel
        'manage_options',                 // kapabilitet
        'skybug_dashboard',              // meny-slug
        'skybug_render_dashboard_page',  // callback
        'dashicons-admin-tools',         // ikon
        1                                // posisjon (høy prioritet)
    );

    // Undermenyer
    add_submenu_page(
        'skybug_dashboard',
        __('Programmer','skybug'), __('Programmer','skybug'),
        'manage_options',
        'skybug_programs_page',
        'skybug_render_programs_page'
    );

    add_submenu_page(
        'skybug_dashboard',
        __('Alle saker','skybug'), __('Saker','skybug'),
        'manage_options',
        'edit.php?post_type=skybug_issue',
        null
    );

    add_submenu_page(
        'skybug_dashboard',
        __('Feilrapporter','skybug'), __('Feilrapporter','skybug'),
        'manage_options',
        'skybug_bugs_page',
        'skybug_render_bugs_page'
    );

    add_submenu_page(
        'skybug_dashboard',
        __('Ønskede funksjoner','skybug'), __('Ønskede funksjoner','skybug'),
        'manage_options',
        'skybug_features_page',
        'skybug_render_features_page'
    );

    add_submenu_page(
        'skybug_dashboard',
        __('Statistikk','skybug'), __('Statistikk','skybug'),
        'manage_options',
        'skybug_stats',
        'skybug_render_stats_page'
    );

    add_submenu_page(
        'skybug_dashboard',
        __('Innstillinger','skybug'), __('Innstillinger','skybug'),
        'manage_options',
        'skybug_settings',
        'skybug_render_settings_page'
    );

    add_submenu_page(
        'skybug_dashboard',
        __('Brukermanual','skybug'), __('Brukermanual','skybug'),
        'manage_options',
        'skybug_manual',
        'skybug_render_manual_page'
    );

    add_submenu_page(
        'skybug_dashboard',
        __('Diverse','skybug'), __('Diverse','skybug'),
        'manage_options',
        'skybug_misc',
        'skybug_render_misc_page'
    );

    add_submenu_page(
        'skybug_dashboard',
        __('API-logger','skybug'), __('API-logger','skybug'),
        'manage_options',
        'skybug_logs',
        'skybug_render_logs_page'
    );
}

// Redirect standard listevisning til moderne kort-side
add_action('admin_init', function(){
    if (!is_admin()) return;
    if (!current_user_can('manage_options')) return;
    if (isset($_GET['post_type']) && $_GET['post_type'] === 'skybug_issue' && !isset($_GET['page'])) {
        // Unngå redirect hvis vi er på ny post eller redigerer en konkret sak
        if (isset($_GET['post']) || (isset($_GET['action']) && $_GET['action'] !== '')) return;
        // Bare redirect på hovedliste (edit.php?post_type=skybug_issue)
        wp_safe_redirect( admin_url('admin.php?page=skybug_bugs_page') );
        exit;
    }
    // Redirect program liste til moderne side (unntatt enkelt redigering / ny)
    if (isset($_GET['post_type']) && $_GET['post_type'] === 'skybug_program' && !isset($_GET['page'])) {
        if (isset($_GET['post']) || (isset($_GET['action']) && $_GET['action'] !== '')) return;
        wp_safe_redirect( admin_url('admin.php?page=skybug_programs_page') );
        exit;
    }
});
# slutt 2b3c4d5e

// Dashboard side callback
# 3c4d5e6f - Render SkyBug Dashboard side med moderne design - se AI-learned/funksjonslogg.json
function skybug_render_dashboard_page() {
    // Hent metrics for dashboard
    $program_count = wp_count_posts('skybug_program')->publish;
    $issue_count = wp_count_posts('skybug_issue')->publish;
    
    // Tell bugs og features
    $bugs = get_posts(array(
        'post_type' => 'skybug_issue',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'tax_query' => array(array('taxonomy' => 'skybug_type', 'field' => 'slug', 'terms' => 'bug'))
    ));
    
    $features = get_posts(array(
        'post_type' => 'skybug_issue',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'tax_query' => array(array('taxonomy' => 'skybug_type', 'field' => 'slug', 'terms' => 'feature'))
    ));
    
    $closed = get_posts(array(
        'post_type'=>'skybug_issue',
        'post_status'=>array('skybug_closed','skybug_resolved'),
        'fields'=>'ids',
        'numberposts'=>-1
    ));
    
    $bug_count = count($bugs);
    $feature_count = count($features);
    // Merk: Teller både skybug_resolved og skybug_closed som "Løste Saker".
    $closed_count = count($closed);
    
    // Hent nylig aktivitet (siste 10 saker)
    $recent_issues = get_posts(array(
        'post_type' => 'skybug_issue',
        'post_status' => array('publish','skybug_in_progress','skybug_waiting','skybug_resolved','skybug_closed'),
        'posts_per_page' => 10,
        'orderby' => 'date',
        'order' => 'DESC'
    ));
    
    $t0 = microtime(true);
    if(!defined('SKYBUG_SETTINGS_DEBUG')) define('SKYBUG_SETTINGS_DEBUG', true);
    if(SKYBUG_SETTINGS_DEBUG) error_log('[SkyBug][SETTINGS] START t='.number_format($t0,5));
    echo '<div class="skybug-dashboard-wrapper skybug-container">';
    
    // Dashboard header
    echo '<div class="skybug-dashboard-header">';
    echo '<h1 class="skybug-dashboard-title">' . esc_html__('SkyBug Dashboard', 'skybug') . '</h1>';
    echo '<p class="skybug-dashboard-subtitle">' . esc_html__('Oversikt over alle programmer, bugs og funksjoner', 'skybug') . '</p>';
    echo '</div>';
    
    // Metrics cards
    echo '<div class="skybug-metrics-grid">';
    
    // Programs metric
    echo '<div class="skybug-metric-card">';
    echo '<div class="skybug-metric-header">';
    echo '<div class="skybug-metric-icon programs">📦</div>';
    echo '<h3 class="skybug-metric-title">' . esc_html__('Programmer', 'skybug') . '</h3>';
    echo '</div>';
    echo '<div class="skybug-metric-content">';
    echo '<div class="skybug-metric-value">' . $program_count . '</div>';
    echo '<div class="skybug-metric-label">' . esc_html__('Totalt registrerte', 'skybug') . '</div>';
    echo '</div>';
    echo '</div>';
    
    // Open bugs metric
    echo '<div class="skybug-metric-card">';
    echo '<div class="skybug-metric-header">';
    echo '<div class="skybug-metric-icon bugs">🐛</div>';
    echo '<h3 class="skybug-metric-title">' . esc_html__('Åpne Bugs', 'skybug') . '</h3>';
    echo '</div>';
    echo '<div class="skybug-metric-content">';
    echo '<div class="skybug-metric-value">' . $bug_count . '</div>';
    echo '<div class="skybug-metric-label">' . esc_html__('Krever oppmerksomhet', 'skybug') . '</div>';
    echo '</div>';
    echo '</div>';
    
    // Feature requests metric
    echo '<div class="skybug-metric-card">';
    echo '<div class="skybug-metric-header">';
    echo '<div class="skybug-metric-icon features">✨</div>';
    echo '<h3 class="skybug-metric-title">' . esc_html__('Ønskede Funksjoner', 'skybug') . '</h3>';
    echo '</div>';
    echo '<div class="skybug-metric-content">';
    echo '<div class="skybug-metric-value">' . $feature_count . '</div>';
    echo '<div class="skybug-metric-label">' . esc_html__('Venter på utvikling', 'skybug') . '</div>';
    echo '</div>';
    echo '</div>';
    
    // Resolved issues metric
    echo '<div class="skybug-metric-card">';
    echo '<div class="skybug-metric-header">';
    echo '<div class="skybug-metric-icon resolved">✅</div>';
    echo '<h3 class="skybug-metric-title">' . esc_html__('Løste Saker', 'skybug') . '</h3>';
    echo '</div>';
    echo '<div class="skybug-metric-content">';
    echo '<div class="skybug-metric-value">' . $closed_count . '</div>';
    echo '<div class="skybug-metric-label">' . esc_html__('Fullført og lukket', 'skybug') . '</div>';
    echo '</div>';
    echo '</div>';
    
    echo '</div>'; // End metrics grid
    
    // API Status Box
    echo '<div class="skybug-api-status-box">';
    echo '<h3 class="skybug-status-title">' . esc_html__('API og System Status', 'skybug') . '</h3>';
    
    // Get programs with API connections
    $api_programs = get_posts([
        'post_type' => 'skybug_program',
        'meta_query' => [
            [
                'key' => '_skybug_api_enabled',
                'value' => '1',
                'compare' => '='
            ],
            [
                'key' => '_skybug_api_key',
                'value' => '',
                'compare' => '!='
            ]
        ],
        'numberposts' => -1
    ]);
    
    $api_count = count($api_programs);
    echo '<div class="skybug-status-item">';
    echo '<span class="skybug-status-icon">🔗</span>';
    echo '<span class="skybug-status-text">';
    echo '<strong>' . esc_html__('Programmer tilkoblet API:', 'skybug') . '</strong> ' . $api_count;
    echo '</span>';
    echo '</div>';
    
    // Get recent error logs from API
    $recent_errors = skybug_get_recent_api_errors(3);
    $error_count = count($recent_errors);
    
    echo '<div class="skybug-status-item">';
    echo '<span class="skybug-status-icon">' . ($error_count > 0 ? '⚠️' : '✅') . '</span>';
    echo '<span class="skybug-status-text">';
    echo '<strong>' . esc_html__('API Feilmeldinger:', 'skybug') . '</strong> ';
    if ($error_count > 0) {
        echo $error_count . ' ' . esc_html__('siste feilmeldinger', 'skybug');
        $latest_error = $recent_errors[0];
        if ($latest_error) {
            echo '<br><small>' . esc_html($latest_error['message']);
            if ($latest_error['timestamp']) {
                $time_ago = human_time_diff(strtotime($latest_error['timestamp']), current_time('timestamp'));
                echo ' - ' . $time_ago . ' siden';
            }
            echo '</small>';
        }
    } else {
        echo esc_html__('Ingen nylige API-feil', 'skybug');
    }
    echo '</span>';
    echo '</div>';
    
    // Get last program update (look at all recent issues, not just API programs)
    $last_program_activity = null;
    $last_program_name = '';
    
    // Get the most recent issue across all programs
    $recent_issues = get_posts([
        'post_type' => 'skybug_issue',
        'post_status' => 'publish',
        'numberposts' => 1,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);
    
    if (!empty($recent_issues)) {
        $latest_issue = $recent_issues[0];
        $program_id = get_post_meta($latest_issue->ID, '_skybug_program_id', true);
        if ($program_id) {
            $program_name = get_the_title($program_id);
            $last_program_activity = strtotime($latest_issue->post_date);
            $last_program_name = $program_name;
        }
    }
    
    // Also check for any program with recent _skybug_last_activity meta
    $all_programs = get_posts([
        'post_type' => 'skybug_program',
        'numberposts' => -1,
        'meta_key' => '_skybug_last_activity',
        'orderby' => 'meta_value_num',
        'order' => 'DESC'
    ]);
    
    if (!empty($all_programs)) {
        $latest_program = $all_programs[0];
        $program_activity = get_post_meta($latest_program->ID, '_skybug_last_activity', true);
        if ($program_activity && (!$last_program_activity || $program_activity > $last_program_activity)) {
            $last_program_activity = $program_activity;
            $last_program_name = get_the_title($latest_program->ID);
        }
    }
    
    echo '<div class="skybug-status-item">';
    echo '<span class="skybug-status-icon">⏰</span>';
    echo '<span class="skybug-status-text">';
    echo '<strong>' . esc_html__('Siste oppdatering:', 'skybug') . '</strong> ';
    if ($last_program_activity) {
        $time_diff = human_time_diff($last_program_activity, current_time('timestamp'));
        echo sprintf(
            esc_html__('program %s hadde aktivitet for %s siden', 'skybug'),
            '<em>' . esc_html($last_program_name) . '</em>',
            $time_diff
        );
    } else {
        echo esc_html__('Ingen nylige oppdateringer', 'skybug');
    }
    echo '</span>';
    echo '</div>';
    
    echo '</div>'; // End API status box
    
    // Dashboard widgets
    echo '<div class="skybug-widgets-grid">';
    
    // Trend chart widget
    echo '<div class="skybug-chart-card">';
    echo '<div class="skybug-chart-header">';
    echo '<h3 class="skybug-chart-title">' . esc_html__('Utvikling siste 6 måneder', 'skybug') . '</h3>';
    echo '<div class="skybug-chart-controls">';
    echo '<button class="skybug-chart-toggle active" data-chart-type="trend">' . esc_html__('Trend', 'skybug') . '</button>';
    echo '<button class="skybug-chart-toggle" data-chart-type="status">' . esc_html__('Status', 'skybug') . '</button>';
    echo '</div>';
    echo '</div>';
    echo '<div class="skybug-chart-container">';
    echo '<canvas id="skyBugTrendChart" class="skybug-chart-canvas"></canvas>';
    echo '</div>';
    echo '</div>';
    
    // Recent activity widget
    echo '<div class="skybug-activity-widget">';
    echo '<div class="skybug-activity-header">';
    echo '<h3 class="skybug-activity-title">' . esc_html__('Siste aktivitet', 'skybug') . '</h3>';
    echo '</div>';
    echo '<div class="skybug-activity-list">';
    
    if (!empty($recent_issues)) {
        foreach (array_slice($recent_issues, 0, 8) as $issue) {
            $program_id = get_post_meta($issue->ID, '_skybug_program_id', true);
            $program_name = $program_id ? get_the_title($program_id) : __('Ukjent program', 'skybug');
            $issue_types = wp_get_post_terms($issue->ID, 'skybug_type');
            $type = !empty($issue_types) ? $issue_types[0]->slug : 'unknown';
            
            echo '<div class="skybug-activity-item">';
            echo '<div class="skybug-activity-icon ' . esc_attr($type) . '">';
            echo $type === 'bug' ? '🐛' : ($type === 'feature' ? '✨' : '📋');
            echo '</div>';
            echo '<div class="skybug-activity-content">';
            echo '<p class="skybug-activity-message">' . esc_html($issue->post_title) . '</p>';
            echo '<div class="skybug-activity-meta">';
            echo '<span>' . human_time_diff(strtotime($issue->post_date), current_time('timestamp')) . ' ago</span>';
            echo '<span>•</span>';
            echo '<span>' . esc_html($program_name) . '</span>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<div class="skybug-activity-item">';
        echo '<p>' . esc_html__('Ingen nylige aktiviteter', 'skybug') . '</p>';
        echo '</div>';
    }
    
    echo '</div>'; // End activity list
    echo '</div>'; // End activity widget
    
    echo '</div>'; // End widgets grid
    
    // Quick actions
    echo '<div class="skybug-quick-actions">';
    echo '<a href="' . admin_url('post-new.php?post_type=skybug_issue') . '" class="skybug-quick-action">';
    echo '<span class="skybug-quick-action-icon">➕</span>';
    echo esc_html__('Ny sak', 'skybug');
    echo '</a>';
    echo '<a href="' . admin_url('post-new.php?post_type=skybug_program') . '" class="skybug-quick-action">';
    echo '<span class="skybug-quick-action-icon">📦</span>';
    echo esc_html__('Nytt program', 'skybug');
    echo '</a>';
    echo '<a href="' . admin_url('admin.php?page=skybug_stats') . '" class="skybug-quick-action">';
    echo '<span class="skybug-quick-action-icon">📊</span>';
    echo esc_html__('Vis statistikk', 'skybug');
    echo '</a>';
    echo '</div>';
    
    echo '</div>'; // End dashboard wrapper
}
# slutt 3c4d5e6f

// Bug Reports side callback
# 4d5e6f7g - Render Bug Reports side med moderne design - se AI-learned/funksjonslogg.json
function skybug_render_bugs_page() {
    $bugs = array();
    $bug_query_args = array(
        'post_type' => 'skybug_issue',
        'posts_per_page' => -1,
        'post_status' => array('publish', 'skybug_in_progress', 'skybug_waiting', 'skybug_resolved', 'skybug_closed'),
        'tax_query' => array(
            array(
                'taxonomy' => 'skybug_type',
                'field' => 'slug',
                'terms' => array('bug')
            )
        ),
        'orderby' => 'ID',
        'order' => 'DESC'
    );
    $bugs = get_posts($bug_query_args);

    $debug_reason = '';

    // Fallback: hvis ingen funnet, forsøk å hente saker uten term og anta bug
    if (empty($bugs)) {
        global $wpdb;
        $debug_reason = 'initial_tax_query_empty';
        $untyped_ids = $wpdb->get_col($wpdb->prepare("SELECT p.ID FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
            LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = %s
            WHERE tt.term_taxonomy_id IS NULL AND p.post_type = %s AND p.post_status IN ('publish','skybug_in_progress','skybug_waiting','skybug_resolved','skybug_closed')",
            'skybug_type', 'skybug_issue'));
        if (!empty($untyped_ids)) {
            // Sett term 'bug' på alle utypede for å normalisere data
            foreach ($untyped_ids as $uid) {
                wp_set_post_terms($uid, array('bug'), 'skybug_type', false);
            }
            // Hent på nytt
            if (!empty($untyped_ids)) {
                $bugs = get_posts(array(
                    'post_type' => 'skybug_issue',
                    'posts_per_page' => -1,
                    'post__in' => $untyped_ids,
                    'orderby' => 'ID',
                    'order' => 'DESC'
                ));
                $debug_reason = 'assigned_missing_terms';
            }
        }
    }

    // Ekstra fallback: direkte SQL count hvis fortsatt tomt
    if (empty($bugs)) {
        global $wpdb;
        $sql = $wpdb->prepare("SELECT p.ID FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
            WHERE p.post_type = %s
            AND p.post_status IN ('publish','skybug_in_progress','skybug_waiting','skybug_resolved','skybug_closed')
            AND tt.taxonomy = 'skybug_type'
            AND t.slug = 'bug'",
            'skybug_issue');
        $ids = $wpdb->get_col($sql);
        if (!empty($ids)) {
            $bugs = get_posts(array(
                'post_type' => 'skybug_issue',
                'posts_per_page' => -1,
                'post__in' => $ids,
                'orderby' => 'ID',
                'order' => 'DESC'
            ));
            $debug_reason = 'direct_sql_recovery';
        }
    }

    // Siste fallback: hent alle saker og filtrer i PHP dersom fortsatt tomt
    if (empty($bugs)) {
        $all = get_posts(array(
            'post_type' => 'skybug_issue',
            'posts_per_page' => -1,
            'post_status' => array('publish', 'skybug_in_progress', 'skybug_waiting', 'skybug_resolved', 'skybug_closed')
        ));
        $php_filtered = array();
        foreach($all as $p){
            $terms = wp_get_post_terms($p->ID, 'skybug_type');
            if (empty($terms)) continue;
            foreach($terms as $t){ if ($t->slug === 'bug'){ $php_filtered[] = $p; break; } }
        }
        if (!empty($php_filtered)) { $bugs = $php_filtered; $debug_reason = 'php_filter_fallback'; }
    }

    // Logg hvis debugging aktivt (enkelt filappend)
    if (empty($bugs)) {
        $log_entry = date('c') . " BUG_PAGE_EMPTY reason={$debug_reason} args=" . json_encode($bug_query_args) . "\n";
        @file_put_contents(WP_CONTENT_DIR . '/skybug-debug.log', $log_entry, FILE_APPEND);
    }

    // Finn start på eksisterende output blokk og behold den.
    echo '<div class="wrap skybug-modern-page">';
    echo '<h1 class="skybug-page-title">';
    echo '<span class="skybug-title-icon">🐛</span>';
    echo esc_html__('Feilrapporter', 'skybug');
    echo '<span class="skybug-title-count">(' . count($bugs) . ')</span>';
    echo '</h1>';

    // Recent IMAP emails box (only if IMAP configured)
    $imap_enabled = get_option('skybug_imap_enabled', false);
    $imap_host = get_option('skybug_imap_host', '');
    $imap_username = get_option('skybug_imap_username', '');
    $imap_configured = !empty($imap_host) && !empty($imap_username);
    
    // Debug logging for IMAP config verification
    $debug_imap = date('c') . " BUGS_PAGE_IMAP enabled=" . var_export($imap_enabled, true) . 
                  " host='" . $imap_host . "' username='" . $imap_username . "' configured=" . var_export($imap_configured, true) . "\n";
    @file_put_contents(WP_CONTENT_DIR . '/skybug-imap.log', $debug_imap, FILE_APPEND);
    
    if ($imap_enabled && $imap_configured) {
        echo '<div class="skybug-recent-emails-box" style="margin:8px 0 20px;max-width:620px;background:#fff;border:1px solid #e2e6ea;border-radius:8px;padding:14px 16px;box-shadow:0 1px 2px rgba(0,0,0,.04);">';
        echo '<div style="display:flex;align-items:center;gap:8px;justify-content:space-between;flex-wrap:wrap">';
        echo '<div style="display:flex;align-items:center;gap:8px"><span style="font-size:18px">📥</span><h2 style="margin:0;font-size:16px;line-height:1.2">' . esc_html__('Siste 5 E-poster','skybug') . '</h2></div>';
        echo '<button type="button" class="button" id="skybug-refresh-recent-emails">' . esc_html__('Oppdater','skybug') . '</button>';
        echo '</div>';
        echo '<ul id="skybug-recent-emails-list" style="margin:12px 0 0;padding:0;list-style:none;font-size:13px;line-height:1.4;min-height:40px"><li style="color:#6c757d;font-style:italic">' . esc_html__('Laster...','skybug') . '</li></ul>';
        echo '</div>';
    }

    if ($imap_enabled && $imap_configured) {
        echo '<div class="notice notice-success" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-left: 4px solid #28a745;">';
        echo '<p><span style="font-size: 16px;">🟢</span> <strong>IMAP Status:</strong> IMAP Aktiv - Mottar automatisk nye saker via e-post</p>';
        echo '</div>';
    }

    if (empty($bugs)) {
        echo '<div class="skybug-empty-state-modern">';
        echo '<div class="skybug-empty-icon-large">🐛</div>';
        echo '<h2>' . esc_html__('Ingen feilrapporter funnet', 'skybug') . '</h2>';
        echo '<p>' . esc_html__('Det er ingen registrerte bugs med type "Feilrapport" ennå.', 'skybug') . '</p>';
        echo '<a href="' . admin_url('post-new.php?post_type=skybug_issue') . '" class="button button-primary button-large">';
        echo '<span style="margin-right: 8px;">➕</span>' . esc_html__('Legg til ny sak', 'skybug');
        echo '</a>';
        echo '</div>';
        echo '</div>';
        return;
    }

    // Legg inn enkel filter/søk (for videre utbygging)
    echo '<form method="get" style="margin: 0 0 16px 0; display: flex; gap: 8px; align-items: center;">';
    echo '<input type="hidden" name="page" value="skybug_bugs_page" />';
    echo '<input type="text" name="s" placeholder="' . esc_attr__('Søk i tittel...', 'skybug') . '" value="' . esc_attr(isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '') . '" style="min-width:240px;" />';
    echo '<select name="status">';
    echo '<option value="">' . esc_html__('Alle statuser', 'skybug') . '</option>';
    $statuses = array('publish'=>'Ny/Åpen','skybug_in_progress'=>'Under arbeid','skybug_waiting'=>'Venter','skybug_resolved'=>'Løst','skybug_closed'=>'Lukket');
    $sel_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
    foreach($statuses as $key=>$label){
        echo '<option value="' . esc_attr($key) . '"' . selected($sel_status,$key,false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
    echo '<button class="button">' . esc_html__('Filter', 'skybug') . '</button>';
    echo '</form>';

    // Enkel filter logikk i minnet
    $filtered = array();
    $search = isset($_GET['s']) ? mb_strtolower(sanitize_text_field($_GET['s'])) : '';
    foreach($bugs as $b){
        if ($search && mb_strpos(mb_strtolower($b->post_title), $search) === false) continue;
        if ($sel_status && $b->post_status !== $sel_status) continue;
        $filtered[] = $b;
    }
    $bugs = $filtered;

    echo '<div class="skybug-bugs-toolbar" id="skybug-bugs-toolbar" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:18px">';
    
    // Bulk actions form
    echo '<form id="bulk-action-form-bugs">';
    echo '<div class="bulk-actions-container">';
    echo '<div class="bulk-actions-row">';
    echo '<div class="bulk-select-controls">';
    echo '<button type="button" class="bulk-select-all button">' . esc_html__('Velg alle', 'skybug') . '</button>';
    echo '<button type="button" class="bulk-clear-all button">' . esc_html__('Rens alle', 'skybug') . '</button>';
    echo '</div>';
    echo '<select id="bulk-action-select-bugs">';
    echo '<option value="">' . esc_html__('Bulk handlinger', 'skybug') . '</option>';
    echo '<option value="move-to-feature">' . esc_html__('Flytt til Features', 'skybug') . '</option>';
    echo '<option value="move-to-undersokes">' . esc_html__('Flytt til Undersøkes', 'skybug') . '</option>';
    echo '<option value="status-in-progress">' . esc_html__('Sett status: Under arbeid', 'skybug') . '</option>';
    echo '<option value="status-resolved">' . esc_html__('Sett status: Løst', 'skybug') . '</option>';
    echo '<option value="status-closed">' . esc_html__('Sett status: Lukket', 'skybug') . '</option>';
    echo '<option value="delete-issues">' . esc_html__('Slett valgte', 'skybug') . '</option>';
    echo '</select>';
    echo '<button type="submit" id="bulk-action-submit-bugs" class="button-primary" disabled>' . esc_html__('Utfør', 'skybug') . '</button>';
    echo '</div>';
    echo '</div>';
    echo '</form>';
    
    // Primære handlinger
    echo '<div class="skybug-bugs-actions" style="display:flex;gap:8px;align-items:center">';
    echo '<a href="' . esc_url(admin_url('post-new.php?post_type=skybug_issue')) . '" class="button button-primary">➕ ' . esc_html__('Ny Sak','skybug') . '</a>';
    $imap_enabled_btn = ($imap_enabled && $imap_configured);
    echo '<button type="button" class="button" id="skybug-fetch-imap" ' . ( $imap_enabled_btn ? '' : 'disabled' ) . '>📥 ' . esc_html__('Hent fra IMAP','skybug') . '</button>';
    echo '</div>';
    echo '<div class="skybug-filter-chips" data-component="chips" style="display:flex;gap:6px;flex-wrap:wrap">';
    $chip_statuses = array(
        'all' => __('Alle','skybug'),
        'publish' => __('Ny','skybug'),
        'skybug_in_progress' => __('Arbeid','skybug'),
        'skybug_waiting' => __('Venter','skybug'),
        'skybug_resolved' => __('Løst','skybug'),
        'skybug_closed' => __('Lukket','skybug')
    );
    foreach($chip_statuses as $key=>$label){
        echo '<button type="button" class="skybug-chip" data-filter-status="' . esc_attr($key) . '">' . esc_html($label) . '</button>';
    }
    echo '</div>';
    echo '<div style="margin-left:auto;display:flex;gap:8px;align-items:center">';
    echo '<input type="text" id="skybug-search" placeholder="' . esc_attr__('Søk...','skybug') . '" style="min-width:200px" />';
    echo '<select id="skybug-priority-filter"><option value="">' . esc_html__('Alle prioriteter','skybug') . '</option><option value="critical">' . esc_html__('Kritisk','skybug') . '</option><option value="high">' . esc_html__('Høy','skybug') . '</option><option value="medium">' . esc_html__('Middels','skybug') . '</option><option value="low">' . esc_html__('Lav','skybug') . '</option></select>';
    echo '</div>';
    echo '</div>';

    echo '<div class="skybug-bug-grid" id="skybug-bug-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px;align-items:start">';
    foreach($bugs as $bug){
        $program_id = get_post_meta($bug->ID, '_skybug_program_id', true);
        $program_name = $program_id ? get_the_title($program_id) : '';
        $priority = get_post_meta($bug->ID, '_skybug_priority', true) ?: 'medium';
        $assigned_user = get_post_meta($bug->ID, '_skybug_assigned_user', true);
        $reporter_email = get_post_meta($bug->ID, '_skybug_reporter_email', true);
        $reporter_name = get_post_meta($bug->ID, '_skybug_reporter_name', true);
        $last_activity = get_post_meta($bug->ID, '_skybug_last_activity', true);
        $status = $bug->post_status;
        $status_meta = array(
            'publish' => array('label'=>__('Ny/Åpen','skybug'),'color'=>'#dc3545','icon'=>'🆕'),
            'skybug_in_progress' => array('label'=>__('Under arbeid','skybug'),'color'=>'#fd7e14','icon'=>'🔧'),
            'skybug_waiting' => array('label'=>__('Venter','skybug'),'color'=>'#ffc107','icon'=>'⏳'),
            'skybug_resolved' => array('label'=>__('Løst','skybug'),'color'=>'#20c997','icon'=>'✅'),
            'skybug_closed' => array('label'=>__('Lukket','skybug'),'color'=>'#6c757d','icon'=>'🔒')
        );
        $st = $status_meta[$status] ?? $status_meta['publish'];
        if ($last_activity) { $diff = human_time_diff($last_activity, current_time('timestamp')); }
        else { $post_date = strtotime($bug->post_date); $diff = human_time_diff($post_date, current_time('timestamp')); }
        $priority_meta = array(
            'low'=>array('label'=>__('Lav','skybug'),'color'=>'#28a745','icon'=>'⬇️'),
            'medium'=>array('label'=>__('Middels','skybug'),'color'=>'#ffc107','icon'=>'➡️'),
            'high'=>array('label'=>__('Høy','skybug'),'color'=>'#fd7e14','icon'=>'⬆️'),
            'critical'=>array('label'=>__('Kritisk','skybug'),'color'=>'#dc3545','icon'=>'🚨')
        );
        $pm = $priority_meta[$priority] ?? $priority_meta['medium'];
        $assigned_label = $assigned_user ? ( ($u=get_user_by('id',$assigned_user)) ? esc_html($u->display_name) : __('Ukjent','skybug') ) : __('Ingen','skybug');
        $reporter_label = $reporter_name ? esc_html($reporter_name) : __('Intern','skybug');
        $edit_url = get_edit_post_link($bug->ID);
        echo '<div class="skybug-bug-card" data-status="' . esc_attr($status) . '" data-priority="' . esc_attr($priority) . '" data-search="' . esc_attr(mb_strtolower($bug->post_title . ' ' . $reporter_label)) . '">';
    echo '<div class="skybug-bug-card-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">';
    echo '<span><input type="checkbox" name="selected_issues[]" class="skybug-select-bug issue-checkbox" value="' . $bug->ID . '" data-issue-id="' . $bug->ID . '" /></span>';
        echo '<span class="skybug-bug-id" style="font-size:12px;color:#6c757d">#' . $bug->ID . '</span>';
        echo '<span class="skybug-status-pill" style="background:' . $st['color'] . ';color:#fff;font-size:11px;padding:2px 8px;border-radius:12px;display:inline-flex;align-items:center;gap:4px">' . $st['icon'] . '<span>' . esc_html($st['label']) . '</span></span>';
        echo '</div>';
        echo '<h3 class="skybug-bug-title" style="margin:0 0 6px;font-size:15px;line-height:1.3"><a href="' . esc_url($edit_url) . '" style="text-decoration:none">' . esc_html($bug->post_title) . '</a></h3>';
        echo '<div class="skybug-bug-meta" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px">';
        echo '<span class="skybug-priority-tag" style="background:' . $pm['color'] . ';color:#fff;padding:2px 6px;border-radius:6px;font-size:11px;display:inline-flex;align-items:center;gap:4px">' . $pm['icon'] . ' ' . esc_html($pm['label']) . '</span>';
        if($program_name){ echo '<span class="skybug-program-tag" style="background:#343a40;color:#fff;padding:2px 6px;border-radius:6px;font-size:11px;display:inline-block">' . esc_html($program_name) . '</span>'; }
        echo '<span class="skybug-assigned-tag" style="background:#6f42c1;color:#fff;padding:2px 6px;border-radius:6px;font-size:11px">👤 ' . $assigned_label . '</span>';
        echo '</div>';
        echo '<div class="skybug-bug-footer" style="display:flex;justify-content:space-between;align-items:center;font-size:11px;color:#6c757d">';
        echo '<span>' . sprintf(__('%s siden','skybug'), esc_html($diff)) . '</span>';
        echo '<div class="skybug-card-actions" style="display:flex;gap:4px">';
        if ($status === 'publish') { echo '<button class="skybug-mini-action" data-post-id="' . $bug->ID . '" data-status="skybug_in_progress" title="' . esc_attr__('Start arbeid','skybug') . '">🔧</button>'; }
        elseif ($status === 'skybug_in_progress') { echo '<button class="skybug-mini-action" data-post-id="' . $bug->ID . '" data-status="skybug_resolved" title="' . esc_attr__('Marker som løst','skybug') . '">✅</button><button class="skybug-mini-action" data-post-id="' . $bug->ID . '" data-status="skybug_waiting" title="' . esc_attr__('Venter på svar','skybug') . '">⏳</button>'; }
        elseif ($status === 'skybug_resolved') { echo '<button class="skybug-mini-action" data-post-id="' . $bug->ID . '" data-status="skybug_closed" title="' . esc_attr__('Lukk sak','skybug') . '">🔒</button>'; }
        if ($reporter_email) { echo '<button class="skybug-mini-action skybug-send-email" data-post-id="' . $bug->ID . '" data-email="' . esc_attr($reporter_email) . '" title="' . esc_attr__('Send e-post','skybug') . '">📧</button>'; }
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    echo '</div>'; // grid
    
    // Add JavaScript for bugs page bulk actions
    ?>
    <script>
    console.log('SkyBug Bugs Page JavaScript loading...');
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Bugs page DOMContentLoaded fired');
        
        // Bulk actions functionality
        const bulkActionForm = document.getElementById('bulk-action-form-bugs');
        console.log('Bulk action form (bugs):', bulkActionForm);
        
        if (bulkActionForm) {
            console.log('Setting up bugs bulk actions...');
            const checkboxes = document.querySelectorAll('input[name="selected_issues[]"]');
            const selectAllBtn = document.querySelector('.bulk-select-all');
            const clearAllBtn = document.querySelector('.bulk-clear-all');
            const bulkActionButton = document.getElementById('bulk-action-submit-bugs');
            const bulkActionSelect = document.getElementById('bulk-action-select-bugs');
            
            console.log('Found bugs elements:', {
                checkboxes: checkboxes.length,
                selectAllBtn: !!selectAllBtn,
                clearAllBtn: !!clearAllBtn,
                bulkActionButton: !!bulkActionButton,
                bulkActionSelect: !!bulkActionSelect
            });

            // Select all functionality
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function() {
                    console.log('Bugs: Select all clicked');
                    checkboxes.forEach(cb => cb.checked = true);
                    updateBulkActionButton();
                });
            }
            
            // Clear all functionality
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', function() {
                    console.log('Bugs: Clear all clicked');
                    checkboxes.forEach(cb => cb.checked = false);
                    updateBulkActionButton();
                });
            }
            
            // Update bulk action button state
            function updateBulkActionButton() {
                if (!bulkActionButton || !bulkActionSelect) return;
                
                const checkedCount = document.querySelectorAll('input[name="selected_issues[]"]:checked').length;
                const actionSelected = bulkActionSelect.value !== '';
                bulkActionButton.disabled = checkedCount === 0 || !actionSelected;
                bulkActionButton.textContent = checkedCount > 0 ? 
                    `Utfør (${checkedCount} valgt)` : 'Utfør';
                    
                console.log('Bugs button updated:', {checkedCount, actionSelected, disabled: bulkActionButton.disabled});
            }
            
            // Update button on checkbox change
            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    console.log('Bugs checkbox changed:', this.checked);
                    updateBulkActionButton();
                });
            });
            
            // Update button on action change
            if (bulkActionSelect) {
                bulkActionSelect.addEventListener('change', function() {
                    console.log('Bugs action changed:', this.value);
                    updateBulkActionButton();
                });
            }

            // Handle bulk action form submission
            bulkActionForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Bugs form submitted');
                
                const selectedIds = Array.from(document.querySelectorAll('input[name="selected_issues[]"]:checked'))
                    .map(cb => cb.value);
                const action = bulkActionSelect.value;
                
                console.log('Bugs Selected IDs:', selectedIds);
                console.log('Bugs Action:', action);
                
                if (selectedIds.length === 0) {
                    alert('Vennligst velg minst én issue');
                    return;
                }
                
                if (!action) {
                    alert('Vennligst velg en handling');
                    return;
                }
                
                // Show confirmation
                let actionText = '';
                switch(action) {
                    case 'move-to-feature': actionText = 'flytte til Features kategori'; break;
                    case 'move-to-undersokes': actionText = 'flytte til Undersøkes kategori'; break;
                    case 'status-in-progress': actionText = 'sette status til Under arbeid'; break;
                    case 'status-resolved': actionText = 'sette status til Løst'; break;
                    case 'status-closed': actionText = 'sette status til Lukket'; break;
                    case 'delete-issues': actionText = 'slette (permanent)'; break;
                }
                
                if (!confirm(`Er du sikker på at du vil ${actionText} ${selectedIds.length} issues?`)) {
                    return;
                }
                
                // Perform AJAX request
                console.log('Bugs: Starting AJAX request...');
                bulkActionButton.disabled = true;
                bulkActionButton.textContent = 'Behandler...';
                
                const formData = new FormData();
                formData.append('action', 'skybug_bulk_action');
                formData.append('action_type', action);
                formData.append('nonce', '<?php echo wp_create_nonce('skybug_bulk_action'); ?>');
                selectedIds.forEach(id => formData.append('issue_ids[]', id));
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Bugs response received:', response);
                    return response.json();
                })
                .then(data => {
                    console.log('Bugs response data:', data);
                    if (data.success) {
                        alert(data.data.message);
                        location.reload();
                    } else {
                        alert('Feil: ' + (data.data.message || 'Ukjent feil'));
                    }
                })
                .catch(error => {
                    console.error('Bugs network error:', error);
                    alert('Nettverksfeil: ' + error.message);
                })
                .finally(() => {
                    bulkActionButton.disabled = false;
                    updateBulkActionButton();
                });
            });
            
            // Initialize button state
            updateBulkActionButton();
            console.log('Bugs bulk actions setup complete');
        } else {
            console.log('Bugs bulk action form not found');
        }
        
        // IMAP Recent Emails functionality
        const refreshEmailsBtn = document.getElementById('skybug-refresh-recent-emails');
        const emailsList = document.getElementById('skybug-recent-emails-list');
        
        if (refreshEmailsBtn && emailsList) {
            console.log('Setting up IMAP email functionality...');
            
            // Load recent emails on page load
            loadRecentEmails();
            
            // Handle refresh button click
            refreshEmailsBtn.addEventListener('click', function() {
                console.log('Refresh emails button clicked');
                loadRecentEmails();
            });
            
            function loadRecentEmails() {
                console.log('Loading recent emails...');
                refreshEmailsBtn.disabled = true;
                refreshEmailsBtn.textContent = '<?php echo esc_js(__('Laster...', 'skybug')); ?>';
                
                emailsList.innerHTML = '<li style="color:#6c757d;font-style:italic"><?php echo esc_js(__('Laster...', 'skybug')); ?></li>';
                
                const formData = new FormData();
                formData.append('action', 'skybug_fetch_recent_imap_emails');
                formData.append('nonce', '<?php echo wp_create_nonce('skybug_recent_emails'); ?>');
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('IMAP response received:', response);
                    return response.json();
                })
                .then(data => {
                    console.log('IMAP response data:', data);
                    if (data.success) {
                        displayEmails(data.data.emails || []);
                    } else {
                        emailsList.innerHTML = '<li style="color:#dc3545">Feil: ' + (data.data.message || 'Ukjent feil') + '</li>';
                    }
                })
                .catch(error => {
                    console.error('IMAP network error:', error);
                    emailsList.innerHTML = '<li style="color:#dc3545">Nettverksfeil: ' + error.message + '</li>';
                })
                .finally(() => {
                    refreshEmailsBtn.disabled = false;
                    refreshEmailsBtn.textContent = '<?php echo esc_js(__('Oppdater', 'skybug')); ?>';
                });
            }
            
            function displayEmails(emails) {
                console.log('Displaying emails:', emails);
                if (!emails || emails.length === 0) {
                    emailsList.innerHTML = '<li style="color:#6c757d;font-style:italic"><?php echo esc_js(__('Ingen nye e-poster funnet', 'skybug')); ?></li>';
                    return;
                }
                
                let html = '';
                emails.forEach(email => {
                    const truncatedSubject = email.subject && email.subject.length > 50 ? 
                        email.subject.substring(0, 50) + '...' : (email.subject || 'Ingen emne');
                    const fromName = email.from || 'Ukjent avsender';
                    const date = email.date || '';
                    
                    html += `<li style="padding:4px 0;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center">
                        <div>
                            <strong>${truncatedSubject}</strong><br>
                            <small style="color:#6c757d">Fra: ${fromName}</small>
                        </div>
                        <small style="color:#6c757d">${date}</small>
                    </li>`;
                });
                
                emailsList.innerHTML = html;
            }
            
            console.log('IMAP setup complete');
        } else {
            console.log('IMAP elements not found');
        }
        
        console.log('Bugs JavaScript setup complete');
    });
    </script>
    <?php
    
    echo '</div>';
}
# slutt 4d5e6f7g

// Ønskede funksjoner side callback
# 5e6f7g8h - Render Features side med moderne design - se AI-learned/funksjonslogg.json
function skybug_render_features_page() {
    // Hent alle feature requests
    $features = get_posts(array(
        'post_type' => 'skybug_issue',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'skybug_type',
                'field' => 'slug',
                'terms' => 'feature'
            )
        ),
        'meta_query' => array(
            array(
                'key' => '_skybug_program_id',
                'compare' => 'EXISTS'
            )
        )
    ));
    
    echo '<div class="skybug-programs-wrapper skybug-container">';
    
    // Header
    echo '<div class="skybug-programs-header">';
    echo '<div class="skybug-programs-title-group">';
    echo '<h1>' . esc_html__('Ønskede funksjoner', 'skybug') . '</h1>';
    echo '<p class="skybug-programs-subtitle">' . esc_html__('Alle feature requests og ønsker', 'skybug') . '</p>';
    echo '</div>';
    echo '<div class="skybug-programs-controls">';
    echo '<input type="text" class="skybug-search-input" placeholder="' . esc_attr__('Søk i ønskede funksjoner...', 'skybug') . '">';
    echo '<select class="skybug-filter-select">';
    echo '<option value="all">' . esc_html__('Alle programmer', 'skybug') . '</option>';
    // Legg til program-alternativer
    $programs = get_posts(array('post_type' => 'skybug_program', 'numberposts' => -1));
    foreach ($programs as $program) {
        echo '<option value="' . esc_attr($program->ID) . '">' . esc_html($program->post_title) . '</option>';
    }
    echo '</select>';
    
    // Bulk actions form
    echo '<form id="bulk-action-form">';
    echo '<div class="bulk-actions-container">';
    echo '<div class="bulk-actions-row">';
    echo '<div class="bulk-select-controls">';
    echo '<button type="button" class="bulk-select-all button">' . esc_html__('Velg alle', 'skybug') . '</button>';
    echo '<button type="button" class="bulk-clear-all button">' . esc_html__('Rens alle', 'skybug') . '</button>';
    echo '</div>';
    echo '<select id="bulk-action-select">';
    echo '<option value="">' . esc_html__('Bulk handlinger', 'skybug') . '</option>';
    echo '<option value="move-to-bug">' . esc_html__('Flytt til Bugs', 'skybug') . '</option>';
    echo '<option value="move-to-undersokes">' . esc_html__('Flytt til Undersøkes', 'skybug') . '</option>';
    echo '<option value="close-issues">' . esc_html__('Lukk issues', 'skybug') . '</option>';
    echo '</select>';
    echo '<button type="submit" id="bulk-action-submit" class="button-primary" disabled>' . esc_html__('Utfør', 'skybug') . '</button>';
    echo '</div>';
    echo '</div>';
    echo '</form>';
    echo '</div>';
    echo '</div>';
    
    if (empty($features)) {
        echo '<div class="skybug-empty-state">';
        echo '<div class="skybug-empty-icon">✨</div>';
        echo '<h3 class="skybug-empty-title">' . esc_html__('Ingen ønskede funksjoner', 'skybug') . '</h3>';
        echo '<p class="skybug-empty-message">' . esc_html__('Det er ikke registrert noen feature requests ennå.', 'skybug') . '</p>';
        echo '</div>';
    } else {
        echo '<div class="skybug-programs-grid">';
        
        foreach ($features as $feature) {
            $program_id = get_post_meta($feature->ID, '_skybug_program_id', true);
            $program_name = $program_id ? get_the_title($program_id) : __('Ukjent program', 'skybug');
            $edit_url = get_edit_post_link($feature->ID);
            $status = $feature->post_status === 'skybug_closed' ? 'closed' : 'open';
            $priority = get_post_meta($feature->ID, '_skybug_priority', true) ?: 'medium';
            
            echo '<div class="skybug-program-card" data-program-id="' . esc_attr($program_id) . '" data-issue-id="' . esc_attr($feature->ID) . '">';
            
            // Card checkbox for bulk actions
            echo '<div class="skybug-card-checkbox">';
            echo '<input type="checkbox" name="selected_issues[]" class="issue-checkbox" value="' . esc_attr($feature->ID) . '" data-issue-id="' . esc_attr($feature->ID) . '">';
            echo '</div>';
            
            // Card header
            echo '<div class="skybug-program-card-header">';
            echo '<h3 class="skybug-program-title">';
            echo '<span class="skybug-program-icon">✨</span>';
            echo esc_html($feature->post_title);
            echo '</h3>';
            echo '<p class="skybug-program-description">';
            echo esc_html($program_name);
            if ($feature->post_excerpt) {
                echo ' • ' . esc_html(wp_trim_words($feature->post_excerpt, 15));
            }
            echo '</p>';
            echo '</div>';
            
            // Card stats
            echo '<div class="skybug-program-stats">';
            echo '<div class="skybug-program-stat">';
            echo '<div class="skybug-program-stat-value">' . get_comments_number($feature->ID) . '</div>';
            echo '<div class="skybug-program-stat-label">' . esc_html__('Kommentarer', 'skybug') . '</div>';
            echo '</div>';
            echo '<div class="skybug-program-stat">';
            echo '<div class="skybug-program-stat-value">' . ucfirst($priority) . '</div>';
            echo '<div class="skybug-program-stat-label">' . esc_html__('Prioritet', 'skybug') . '</div>';
            echo '</div>';
            echo '<div class="skybug-program-stat">';
            echo '<div class="skybug-program-stat-value">' . human_time_diff(strtotime($feature->post_date), current_time('timestamp')) . '</div>';
            echo '<div class="skybug-program-stat-label">' . esc_html__('Siden', 'skybug') . '</div>';
            echo '</div>';
            echo '</div>';
            
            // Card status
            echo '<div class="skybug-program-status">';
            echo '<div class="skybug-program-health">';
            if ($status === 'closed') {
                echo '<div class="skybug-health-dot good"></div>';
                echo '<span class="skybug-health-text good">' . esc_html__('Implementert', 'skybug') . '</span>';
            } else {
                echo '<div class="skybug-health-dot warning"></div>';
                echo '<span class="skybug-health-text warning">' . esc_html__('Venter', 'skybug') . '</span>';
            }
            echo '</div>';
            echo '<div class="skybug-program-actions">';
            echo '<a href="' . esc_url($edit_url) . '" class="skybug-program-action" data-action="edit">';
            echo '<span class="skybug-program-action-icon">✏️</span>';
            echo esc_html__('Rediger', 'skybug');
            echo '</a>';
            if ($program_id) {
                echo '<button class="skybug-program-action" onclick="openEditProgramModal(' . esc_attr($program_id) . ')">';
                echo '<span class="skybug-program-action-icon">⚙️</span>';
                echo esc_html__('Program', 'skybug');
                echo '</button>';
            }
            echo '</div>';
            echo '</div>';
            
            echo '</div>'; // End card
        }
        
        echo '</div>'; // End grid
    }
    
    // Add JavaScript for bulk actions and modal
    ?>
    <script>
    console.log('SkyBug Features JavaScript loading...');
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded fired');
        
        // Bulk actions functionality
        const bulkActionForm = document.getElementById('bulk-action-form');
        console.log('Bulk action form:', bulkActionForm);
        
        if (bulkActionForm) {
            console.log('Setting up bulk actions...');
            const checkboxes = document.querySelectorAll('input[name="selected_issues[]"]');
            const selectAllBtn = document.querySelector('.bulk-select-all');
            const clearAllBtn = document.querySelector('.bulk-clear-all');
            const bulkActionButton = document.getElementById('bulk-action-submit');
            const bulkActionSelect = document.getElementById('bulk-action-select');
            
            console.log('Found elements:', {
                checkboxes: checkboxes.length,
                selectAllBtn: !!selectAllBtn,
                clearAllBtn: !!clearAllBtn,
                bulkActionButton: !!bulkActionButton,
                bulkActionSelect: !!bulkActionSelect
            });
            
            // Select all functionality
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function() {
                    console.log('Select all clicked');
                    checkboxes.forEach(cb => cb.checked = true);
                    updateBulkActionButton();
                });
            }
            
            // Clear all functionality
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', function() {
                    console.log('Clear all clicked');
                    checkboxes.forEach(cb => cb.checked = false);
                    updateBulkActionButton();
                });
            }
            
            // Update bulk action button state
            function updateBulkActionButton() {
                if (!bulkActionButton || !bulkActionSelect) return;
                
                const checkedCount = document.querySelectorAll('input[name="selected_issues[]"]:checked').length;
                const actionSelected = bulkActionSelect.value !== '';
                bulkActionButton.disabled = checkedCount === 0 || !actionSelected;
                bulkActionButton.textContent = checkedCount > 0 ? 
                    `Utfør (${checkedCount} valgt)` : 'Utfør';
                    
                console.log('Button updated:', {checkedCount, actionSelected, disabled: bulkActionButton.disabled});
            }
            
            // Update button on checkbox change
            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    console.log('Checkbox changed:', this.checked);
                    updateBulkActionButton();
                });
            });
            
            // Update button on action change
            if (bulkActionSelect) {
                bulkActionSelect.addEventListener('change', function() {
                    console.log('Action changed:', this.value);
                    updateBulkActionButton();
                });
            }
            
            // Handle bulk action form submission
            bulkActionForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Form submitted');
                
                const selectedIds = Array.from(document.querySelectorAll('input[name="selected_issues[]"]:checked'))
                    .map(cb => cb.value);
                const action = bulkActionSelect.value;
                
                console.log('Selected IDs:', selectedIds);
                console.log('Action:', action);
                
                if (selectedIds.length === 0) {
                    alert('Vennligst velg minst én issue');
                    return;
                }
                
                if (!action) {
                    alert('Vennligst velg en handling');
                    return;
                }
                
                // Show confirmation
                let actionText = '';
                switch(action) {
                    case 'move-to-bug': actionText = 'flytte til Bug kategori'; break;
                    case 'move-to-undersokes': actionText = 'flytte til Undersøkes kategori'; break;
                    case 'close-issues': actionText = 'lukke'; break;
                }
                
                if (!confirm(`Er du sikker på at du vil ${actionText} ${selectedIds.length} issues?`)) {
                    return;
                }
                
                // Perform AJAX request
                console.log('Starting AJAX request...');
                bulkActionButton.disabled = true;
                bulkActionButton.textContent = 'Behandler...';
                
                const formData = new FormData();
                formData.append('action', 'skybug_bulk_action');
                formData.append('action_type', action);
                formData.append('nonce', '<?php echo wp_create_nonce('skybug_bulk_action'); ?>');
                selectedIds.forEach(id => formData.append('issue_ids[]', id));
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Response received:', response);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        alert(data.data.message);
                        location.reload();
                    } else {
                        alert('Feil: ' + (data.data.message || 'Ukjent feil'));
                    }
                })
                .catch(error => {
                    console.error('Network error:', error);
                    alert('Nettverksfeil: ' + error.message);
                })
                .finally(() => {
                    bulkActionButton.disabled = false;
                    updateBulkActionButton();
                });
            });
            
            // Initialize button state
            updateBulkActionButton();
            console.log('Bulk actions setup complete');
        } else {
            console.log('Bulk action form not found');
        }
        
        // Edit program modal functionality
        window.openEditProgramModal = function(programId) {
            console.log('Opening modal for program:', programId);
            
            // Create modal if it doesn't exist
            let modal = document.getElementById('edit-program-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'edit-program-modal';
                modal.className = 'edit-program-modal';
                modal.innerHTML = `
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 id="modal-title">Edit Program #${programId}</h3>
                            <button class="modal-close" onclick="closeEditProgramModal()">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div id="modal-loading">Laster programdata...</div>
                            <form id="edit-program-form" style="display: none;">
                                <div class="form-group">
                                    <label for="program-title">Program Navn:</label>
                                    <input type="text" id="program-title" name="title" required>
                                </div>
                                <div class="form-group">
                                    <label for="program-description">Beskrivelse:</label>
                                    <textarea id="program-description" name="description" rows="4"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="program-api-key">API Nøkkel:</label>
                                    <input type="text" id="program-api-key" name="api_key">
                                </div>
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" id="program-api-enabled" name="api_enabled">
                                        API Aktivert
                                    </label>
                                </div>
                                <div class="form-group">
                                    <label for="program-webhook-url">Webhook URL:</label>
                                    <input type="url" id="program-webhook-url" name="webhook_url">
                                </div>
                                <div class="form-group">
                                    <label>Siste Aktivitet:</label>
                                    <span id="program-last-activity">-</span>
                                </div>
                                <div class="form-group">
                                    <label>Statistikk:</label>
                                    <div class="program-stats">
                                        <span id="program-stats">Laster...</span>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="button-primary">Oppdater Program</button>
                                    <button type="button" onclick="closeEditProgramModal()" class="button">Avbryt</button>
                                </div>
                            </form>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
                
                // Add form submit handler
                document.getElementById('edit-program-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    console.log('Program form submitted');
                    
                    const formData = new FormData(this);
                    formData.append('action', 'skybug_update_program');
                    formData.append('program_id', programId);
                    formData.append('nonce', '<?php echo wp_create_nonce('skybug_edit_program'); ?>');
                    
                    const submitBtn = this.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Oppdaterer...';
                    
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        console.log('Update response:', response);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Update data:', data);
                        if (data.success) {
                            alert('Program oppdatert!');
                            closeEditProgramModal();
                            location.reload();
                        } else {
                            alert('Feil: ' + (data.data.message || 'Ukjent feil'));
                        }
                    })
                    .catch(error => {
                        console.error('Update error:', error);
                        alert('Nettverksfeil: ' + error.message);
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Oppdater Program';
                    });
                });
            }
            
            // Load program data
            document.getElementById('modal-loading').style.display = 'block';
            document.getElementById('edit-program-form').style.display = 'none';
            document.getElementById('modal-title').textContent = `Edit Program #${programId}`;
            
            const formData = new FormData();
            formData.append('action', 'skybug_edit_program');
            formData.append('program_id', programId);
            
            console.log('Loading program data...');
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Load response:', response);
                return response.json();
            })
            .then(data => {
                console.log('Load data:', data);
                if (data.success) {
                    const program = data.data.program;
                    
                    // Fill form fields
                    document.getElementById('program-title').value = program.title || '';
                    document.getElementById('program-description').value = program.description || '';
                    document.getElementById('program-api-key').value = program.api_key || '';
                    document.getElementById('program-api-enabled').checked = program.api_enabled == 1;
                    document.getElementById('program-webhook-url').value = program.webhook_url || '';
                    document.getElementById('program-last-activity').textContent = 
                        program.last_activity || 'Aldri';
                    document.getElementById('program-stats').innerHTML = 
                        `Totalt: ${program.stats.total_issues} | Åpne: ${program.stats.open_issues} | Lukkede: ${program.stats.closed_issues}`;
                    
                    // Show form
                    document.getElementById('modal-loading').style.display = 'none';
                    document.getElementById('edit-program-form').style.display = 'block';
                } else {
                    alert('Feil ved lasting av program: ' + (data.data.message || 'Ukjent feil'));
                    closeEditProgramModal();
                }
            })
            .catch(error => {
                console.error('Load error:', error);
                alert('Nettverksfeil: ' + error.message);
                closeEditProgramModal();
            });
            
            // Show modal
            modal.style.display = 'block';
        };
        
        window.closeEditProgramModal = function() {
            console.log('Closing modal');
            const modal = document.getElementById('edit-program-modal');
            if (modal) {
                modal.style.display = 'none';
            }
        };
        
        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            const modal = document.getElementById('edit-program-modal');
            if (e.target === modal) {
                closeEditProgramModal();
            }
        });
        
        console.log('JavaScript setup complete');
    });
    </script>
    <?php
    
    echo '</div>'; // End wrapper
}
# slutt 5e6f7g8h

// AJAX handler for bulk actions
add_action('wp_ajax_skybug_bulk_action', 'skybug_handle_bulk_action');
function skybug_handle_bulk_action() {
    if (!current_user_can('manage_options')) {
        wp_die('Forbidden');
    }
    
    if (!check_ajax_referer('skybug_bulk_action', 'nonce', false)) {
        wp_send_json_error(['message' => 'Invalid nonce']);
    }
    
    $action = sanitize_text_field($_POST['action_type']);
    $issue_ids = array_map('intval', $_POST['issue_ids']);
    
    if (empty($issue_ids)) {
        wp_send_json_error(['message' => 'No issues selected']);
    }
    
    $updated = 0;
    foreach ($issue_ids as $issue_id) {
        switch ($action) {
            case 'move-to-bug':
                wp_set_object_terms($issue_id, 'bug', 'skybug_type');
                $updated++;
                break;
            case 'move-to-feature':
                wp_set_object_terms($issue_id, 'feature', 'skybug_type');
                $updated++;
                break;
            case 'move-to-undersokes':
                wp_set_object_terms($issue_id, 'undersokes', 'skybug_type');
                $updated++;
                break;
            case 'close-issues':
                wp_update_post(['ID' => $issue_id, 'post_status' => 'skybug_closed']);
                $updated++;
                break;
            case 'status-in-progress':
                wp_update_post(['ID' => $issue_id, 'post_status' => 'skybug_in_progress']);
                $updated++;
                break;
            case 'status-resolved':
                wp_update_post(['ID' => $issue_id, 'post_status' => 'skybug_resolved']);
                $updated++;
                break;
            case 'status-closed':
                wp_update_post(['ID' => $issue_id, 'post_status' => 'skybug_closed']);
                $updated++;
                break;
            case 'delete-issues':
                wp_delete_post($issue_id, true);
                $updated++;
                break;
        }
    }
    
    wp_send_json_success(['message' => "Updated {$updated} issues", 'updated' => $updated]);
}

// AJAX handler for edit program
add_action('wp_ajax_skybug_edit_program', 'skybug_handle_edit_program');
function skybug_handle_edit_program() {
    if (!current_user_can('manage_options')) {
        wp_die('Forbidden');
    }
    
    $program_id = intval($_POST['program_id']);
    if (!$program_id) {
        wp_send_json_error(['message' => 'Invalid program ID']);
    }
    
    $program = get_post($program_id);
    if (!$program || $program->post_type !== 'skybug_program') {
        wp_send_json_error(['message' => 'Program not found']);
    }
    
    // Get program details
    $api_key = get_post_meta($program_id, '_skybug_api_key', true);
    $api_enabled = get_post_meta($program_id, '_skybug_api_enabled', true);
    $webhook_url = get_post_meta($program_id, '_skybug_webhook_url', true);
    $last_activity = get_post_meta($program_id, '_skybug_last_activity', true);
    
    // Get program statistics
    $total_issues = get_posts([
        'post_type' => 'skybug_issue',
        'meta_key' => '_skybug_program_id',
        'meta_value' => $program_id,
        'fields' => 'ids',
        'numberposts' => -1
    ]);
    
    $open_issues = get_posts([
        'post_type' => 'skybug_issue',
        'post_status' => 'publish',
        'meta_key' => '_skybug_program_id',
        'meta_value' => $program_id,
        'fields' => 'ids',
        'numberposts' => -1
    ]);
    
    $closed_issues = get_posts([
        'post_type' => 'skybug_issue',
        'post_status' => ['skybug_closed', 'skybug_resolved'],
        'meta_key' => '_skybug_program_id',
        'meta_value' => $program_id,
        'fields' => 'ids',
        'numberposts' => -1
    ]);
    
    wp_send_json_success([
        'program' => [
            'id' => $program_id,
            'title' => $program->post_title,
            'description' => $program->post_content,
            'api_key' => $api_key,
            'api_enabled' => $api_enabled,
            'webhook_url' => $webhook_url,
            'last_activity' => $last_activity,
            'stats' => [
                'total_issues' => count($total_issues),
                'open_issues' => count($open_issues),
                'closed_issues' => count($closed_issues)
            ]
        ]
    ]);
}

// AJAX handler for update program
add_action('wp_ajax_skybug_update_program', 'skybug_handle_update_program');
function skybug_handle_update_program() {
    if (!current_user_can('manage_options')) {
        wp_die('Forbidden');
    }
    
    if (!check_ajax_referer('skybug_edit_program', 'nonce', false)) {
        wp_send_json_error(['message' => 'Invalid nonce']);
    }
    
    $program_id = intval($_POST['program_id']);
    $title = sanitize_text_field($_POST['title']);
    $description = sanitize_textarea_field($_POST['description']);
    $api_key = sanitize_text_field($_POST['api_key']);
    $api_enabled = isset($_POST['api_enabled']) ? 1 : 0;
    $webhook_url = esc_url_raw($_POST['webhook_url']);
    
    // Update post
    $result = wp_update_post([
        'ID' => $program_id,
        'post_title' => $title,
        'post_content' => $description
    ]);
    
    if (is_wp_error($result)) {
        wp_send_json_error(['message' => 'Failed to update program']);
    }
    
    // Update meta
    update_post_meta($program_id, '_skybug_api_key', $api_key);
    update_post_meta($program_id, '_skybug_api_enabled', $api_enabled);
    update_post_meta($program_id, '_skybug_webhook_url', $webhook_url);
    
    wp_send_json_success(['message' => 'Program updated successfully']);
}

// Statistikk side callback
# 6f7g8h9i - Render Statistikk side med moderne design - se AI-learned/funksjonslogg.json
function skybug_render_stats_page() {
    // Hent statistikk data
    $program_count = wp_count_posts('skybug_program')->publish;
    $issue_count = wp_count_posts('skybug_issue')->publish;
    
    $bugs = get_posts(array(
        'post_type' => 'skybug_issue',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'tax_query' => array(array('taxonomy' => 'skybug_type', 'field' => 'slug', 'terms' => 'bug'))
    ));
    
    $features = get_posts(array(
        'post_type' => 'skybug_issue',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'tax_query' => array(array('taxonomy' => 'skybug_type', 'field' => 'slug', 'terms' => 'feature'))
    ));
    
    $closed = get_posts(array(
        'post_type' => 'skybug_issue',
        'post_status' => array('skybug_closed', 'skybug_resolved'), // Include both closed and resolved
        'fields' => 'ids',
        'numberposts' => -1
    ));
    
    $bug_count = count($bugs);
    $feature_count = count($features);
    $closed_count = count($closed);
    
    // Calculate resolution rate: closed issues as percentage of all issues (open + closed)
    $all_issues = get_posts(array(
        'post_type' => 'skybug_issue',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'post_status' => array('publish', 'skybug_in_progress', 'skybug_waiting', 'skybug_resolved', 'skybug_closed')
    ));
    $total_all_issues = count($all_issues);
    $resolution_rate = $total_all_issues > 0 ? round(($closed_count / $total_all_issues) * 100, 1) : 0;
    
    echo '<div class="skybug-stats-wrapper skybug-container">';
    
    // Statistics header
    echo '<div class="skybug-stats-header">';
    echo '<h1 class="skybug-stats-title">' . esc_html__('Statistikk & Analyse', 'skybug') . '</h1>';
    echo '<p class="skybug-stats-subtitle">' . esc_html__('Detaljert oversikt over alle programmer og saker', 'skybug') . '</p>';
    echo '</div>';
    
    // Overview metrics
    echo '<div class="skybug-stats-overview">';
    
    // Total programs
    echo '<div class="skybug-metric-card large">';
    echo '<div class="skybug-metric-header">';
    echo '<div class="skybug-metric-icon programs">📦</div>';
    echo '<h3 class="skybug-metric-title">' . esc_html__('Programmer', 'skybug') . '</h3>';
    echo '</div>';
    echo '<div class="skybug-metric-content">';
    echo '<div class="skybug-metric-value">' . $program_count . '</div>';
    echo '<div class="skybug-metric-label">' . esc_html__('Totalt registrerte', 'skybug') . '</div>';
    echo '</div>';
    echo '</div>';
    
    // Open issues
    echo '<div class="skybug-metric-card large">';
    echo '<div class="skybug-metric-header">';
    echo '<div class="skybug-metric-icon issues">📋</div>';
    echo '<h3 class="skybug-metric-title">' . esc_html__('Åpne saker', 'skybug') . '</h3>';
    echo '</div>';
    echo '<div class="skybug-metric-content">';
    echo '<div class="skybug-metric-value">' . $issue_count . '</div>';
    echo '<div class="skybug-metric-label">' . esc_html__('Totalt åpne', 'skybug') . '</div>';
    echo '<div class="skybug-metric-breakdown">';
    echo '<span class="bugs">' . $bug_count . ' bugs</span>';
    echo '<span class="features">' . $feature_count . ' funksjoner</span>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    // Closed issues
    echo '<div class="skybug-metric-card large">';
    echo '<div class="skybug-metric-header">';
    echo '<div class="skybug-metric-icon resolved">✅</div>';
    echo '<h3 class="skybug-metric-title">' . esc_html__('Løste saker', 'skybug') . '</h3>';
    echo '</div>';
    echo '<div class="skybug-metric-content">';
    echo '<div class="skybug-metric-value">' . $closed_count . '</div>';
    echo '<div class="skybug-metric-label">' . esc_html__('Fullført og lukket', 'skybug') . '</div>';
    echo '</div>';
    echo '</div>';
    
    // Resolution rate
    echo '<div class="skybug-metric-card large">';
    echo '<div class="skybug-metric-header">';
    echo '<div class="skybug-metric-icon rate">📈</div>';
    echo '<h3 class="skybug-metric-title">' . esc_html__('Løsningsgrad', 'skybug') . '</h3>';
    echo '</div>';
    echo '<div class="skybug-metric-content">';
    echo '<div class="skybug-metric-value">' . $resolution_rate . '%</div>';
    echo '<div class="skybug-metric-label">' . esc_html__('Av alle saker', 'skybug') . '</div>';
    echo '</div>';
    echo '</div>';
    
    echo '</div>'; // End overview
    
    // Charts section
    echo '<div class="skybug-charts-section">';
    
    echo '<div class="skybug-charts-header">';
    echo '<h2 class="skybug-charts-title">' . esc_html__('Trendanalyse', 'skybug') . '</h2>';
    echo '<div class="skybug-charts-controls">';
    echo '<div class="skybug-chart-period-selector">';
    echo '<button class="skybug-period-option active" data-period="30d">' . esc_html__('30 dager', 'skybug') . '</button>';
    echo '<button class="skybug-period-option" data-period="90d">' . esc_html__('90 dager', 'skybug') . '</button>';
    echo '<button class="skybug-period-option" data-period="1y">' . esc_html__('1 år', 'skybug') . '</button>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    echo '<div class="skybug-charts-grid">';
    
    // Main trend chart
    echo '<div class="skybug-chart-card">';
    echo '<div class="skybug-chart-header">';
    echo '<h3 class="skybug-chart-title">';
    echo '<span class="skybug-chart-icon">📊</span>';
    echo esc_html__('Saksutvikling over tid', 'skybug');
    echo '</h3>';
    echo '<div class="skybug-chart-controls">';
    echo '<button class="skybug-chart-toggle active" data-chart-type="line">' . esc_html__('Linje', 'skybug') . '</button>';
    echo '<button class="skybug-chart-toggle" data-chart-type="bar">' . esc_html__('Stolpe', 'skybug') . '</button>';
    echo '</div>';
    echo '</div>';
    echo '<div class="skybug-chart-container">';
    echo '<div class="skybug-chart-main large">';
    echo '<canvas id="skyBugMainChart" class="skybug-chart-canvas"></canvas>';
    echo '</div>';
    echo '<div class="skybug-chart-summary">';
    echo '<div class="skybug-summary-stat">';
    echo '<div class="skybug-summary-value">' . $bug_count . '</div>';
    echo '<div class="skybug-summary-label">' . esc_html__('Åpne bugs', 'skybug') . '</div>';
    echo '</div>';
    echo '<div class="skybug-summary-stat">';
    echo '<div class="skybug-summary-value">' . $feature_count . '</div>';
    echo '<div class="skybug-summary-label">' . esc_html__('Funksjoner', 'skybug') . '</div>';
    echo '</div>';
    echo '<div class="skybug-summary-stat">';
    echo '<div class="skybug-summary-value">' . $closed_count . '</div>';
    echo '<div class="skybug-summary-label">' . esc_html__('Løst', 'skybug') . '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    // Distribution chart
    echo '<div class="skybug-chart-card">';
    echo '<div class="skybug-chart-header">';
    echo '<h3 class="skybug-chart-title">';
    echo '<span class="skybug-chart-icon">🥧</span>';
    echo esc_html__('Fordeling per status', 'skybug');
    echo '</h3>';
    echo '</div>';
    echo '<div class="skybug-chart-container">';
    echo '<div class="skybug-chart-main compact">';
    echo '<canvas id="skyBugDistributionChart" class="skybug-chart-canvas"></canvas>';
    echo '</div>';
    echo '<div class="skybug-chart-legend">';
    echo '<p style="font-size:12px;color:#6c757d;margin:8px 0;">' . esc_html__('Viser kun statuser med aktive saker', 'skybug') . '</p>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    echo '</div>'; // End charts grid
    echo '</div>'; // End charts section
    
    // Export actions
    echo '<div class="skybug-stats-actions">';
    echo '<div class="skybug-export-group">';
    echo '<a href="#" class="skybug-export-button" data-export="csv">';
    echo '<span class="skybug-export-icon">📄</span>';
    echo esc_html__('Eksporter CSV', 'skybug');
    echo '</a>';
    echo '<a href="#" class="skybug-export-button" data-export="pdf">';
    echo '<span class="skybug-export-icon">📋</span>';
    echo esc_html__('Eksporter PDF', 'skybug');
    echo '</a>';
    echo '</div>';
    echo '<div class="skybug-stats-meta">';
    echo esc_html__('Sist oppdatert:', 'skybug') . ' ' . date('d.m.Y H:i');
    echo '</div>';
    echo '</div>';
    
    echo '</div>'; // End stats wrapper
}
# slutt 6f7g8h9i

// Innstillinger side callback
# 7g8h9i0j - Render Innstillinger side med moderne design og SMTP/IMAP - se AI-learned/funksjonslogg.json
function skybug_render_settings_page() {
    $t0 = microtime(true);
    if(!defined('SKYBUG_SETTINGS_DEBUG')) {
        define('SKYBUG_SETTINGS_DEBUG', false); // set true temporarily when deeper profiling needed
    }
    if(SKYBUG_SETTINGS_DEBUG) error_log('[SkyBug][SETTINGS] START t='.number_format($t0,5));
    echo '<div class="skybug-dashboard-wrapper skybug-container">';
    
    // Header
    echo '<div class="skybug-dashboard-header">';
    echo '<h1 class="skybug-dashboard-title">' . esc_html__('Innstillinger', 'skybug') . '</h1>';
    echo '<p class="skybug-dashboard-subtitle">' . esc_html__('Konfigurer SkyBug for ditt miljø', 'skybug') . '</p>';
    echo '</div>';
    
    // Status display if settings were saved
    if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
        echo '<div class="skybug-alert skybug-alert-success">';
        echo '<div class="skybug-alert-icon">✅</div>';
        echo '<div class="skybug-alert-content">';
        echo '<div class="skybug-alert-title">' . esc_html__('Innstillinger lagret', 'skybug') . '</div>';
        echo '<div class="skybug-alert-message">' . esc_html__('Alle innstillinger er oppdatert.', 'skybug') . '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    echo '<form method="post" action="options.php">';
    if(SKYBUG_SETTINGS_DEBUG) error_log('[SkyBug][SETTINGS] BEFORE_SETTINGS_FIELDS dt='.number_format(microtime(true)-$t0,5));
    settings_fields('skybug_settings_group');
    
    // Settings cards grid
    echo '<div class="skybug-widgets-grid" style="grid-template-columns: 1fr;">';
    
    // Varslinger card
    echo '<div class="skybug-chart-card">';
    echo '<div class="skybug-chart-header">';
    echo '<h3 class="skybug-chart-title">';
    echo '<span class="skybug-chart-icon">📧</span>';
    echo esc_html__('Varslinger', 'skybug');
    echo '</h3>';
    echo '</div>';
    echo '<div class="skybug-chart-container" style="height: auto; padding: var(--spacing-6);">';
    echo '<p class="skybug-settings-description">' . esc_html__('Konfigurer epost for varsling ved nye saker.', 'skybug') . '</p>';
    
    echo '<div class="skybug-settings-field">';
    echo '<label class="skybug-settings-label">' . esc_html__('Varslings-epost', 'skybug') . '</label>';
    $notify_email = get_option('skybug_notify_email', '');
    echo '<input type="email" name="skybug_notify_email" value="' . esc_attr($notify_email) . '" class="skybug-settings-input" placeholder="terje@smartesider.no" />';
    echo '<p class="skybug-field-description">' . esc_html__('Hvis tomt sendes ingen epostvarsler.', 'skybug') . '</p>';
    echo '</div>';
    
    echo '</div>';
    echo '</div>';
    
    // SMTP Configuration card
    echo '<div class="skybug-chart-card">';
    echo '<div class="skybug-chart-header">';
    echo '<h3 class="skybug-chart-title">';
    echo '<span class="skybug-chart-icon">📤</span>';
    echo esc_html__('SMTP Konfigurasjon', 'skybug');
    echo '</h3>';
    echo '<div class="skybug-chart-controls">';
    echo '<button type="button" class="skybug-test-button" id="test-smtp" data-nonce="' . esc_attr(wp_create_nonce('skybug_test_smtp')) . '">' . esc_html__('Test SMTP', 'skybug') . '</button>';
    echo '</div>';
    echo '</div>';
    echo '<div class="skybug-chart-container" style="height: auto; padding: var(--spacing-6);">';
    echo '<p class="skybug-settings-description">' . esc_html__('Konfigurer SMTP for å sende e-post via ekstern e-postserver.', 'skybug') . '</p>';
    
    $smtp_fields = [
        'skybug_smtp_host' => ['label' => __('SMTP Server', 'skybug'), 'type' => 'text', 'placeholder' => 'mail.smartesider.no', 'description' => __('SMTP server hostname eller IP-adresse.', 'skybug')],
        'skybug_smtp_port' => ['label' => __('SMTP Port', 'skybug'), 'type' => 'number', 'default' => 587, 'description' => __('Standard: 587 (TLS) eller 465 (SSL).', 'skybug')],
        'skybug_smtp_username' => ['label' => __('SMTP Brukernavn', 'skybug'), 'type' => 'text', 'placeholder' => 'bug@smartesider.no', 'description' => __('Vanligvis samme som e-postadresse.', 'skybug')],
        'skybug_smtp_password' => ['label' => __('SMTP Passord', 'skybug'), 'type' => 'password', 'description' => __('E-post konto passord eller app-spesifikk passord.', 'skybug')],
        'skybug_smtp_security' => ['label' => __('SMTP Sikkerhet', 'skybug'), 'type' => 'select', 'options' => ['none' => __('Ingen', 'skybug'), 'tls' => 'TLS', 'ssl' => 'SSL'], 'default' => 'tls', 'description' => __('Anbefalt: TLS for port 587, SSL for port 465.', 'skybug')],
        'skybug_smtp_from_email' => ['label' => __('Fra-adresse', 'skybug'), 'type' => 'email', 'placeholder' => 'bug@smartesider.no', 'description' => __('E-postadresse som brukes som avsender.', 'skybug')],
        'skybug_smtp_from_name' => ['label' => __('Fra-navn', 'skybug'), 'type' => 'text', 'default' => 'SkyBug - Smartesider.no', 'description' => __('Visningsnavn for avsender.', 'skybug')]
    ];
    
    echo '<div class="skybug-settings-grid">';
    foreach ($smtp_fields as $field => $config) {
        echo '<div class="skybug-settings-field">';
        echo '<label class="skybug-settings-label">' . esc_html($config['label']) . '</label>';
        
        $value = get_option($field, isset($config['default']) ? $config['default'] : '');
        
        if ($config['type'] === 'select') {
            echo '<select name="' . $field . '" class="skybug-settings-input">';
            foreach ($config['options'] as $opt_value => $opt_label) {
                echo '<option value="' . esc_attr($opt_value) . '"' . selected($value, $opt_value, false) . '>' . esc_html($opt_label) . '</option>';
            }
            echo '</select>';
        } else {
            $input_type = $config['type'] === 'number' ? 'number' : ($config['type'] === 'email' ? 'email' : ($config['type'] === 'password' ? 'password' : 'text'));
            $extra_attrs = $config['type'] === 'number' ? ' min="1" max="65535"' : '';
            $placeholder = isset($config['placeholder']) ? ' placeholder="' . esc_attr($config['placeholder']) . '"' : '';
            echo '<input type="' . $input_type . '" name="' . $field . '" value="' . esc_attr($value) . '" class="skybug-settings-input"' . $extra_attrs . $placeholder . ' />';
        }
        
        if (isset($config['description'])) {
            echo '<p class="skybug-field-description">' . esc_html($config['description']) . '</p>';
        }
        
        echo '</div>';
    }
    echo '</div>';
    
    // Test result for SMTP
    echo '<div id="smtp-test-result" class="skybug-test-result"></div>';
    
    echo '</div>';
    echo '</div>';
    
    // IMAP Configuration card
    echo '<div class="skybug-chart-card">';
    echo '<div class="skybug-chart-header">';
    echo '<h3 class="skybug-chart-title">';
    echo '<span class="skybug-chart-icon">📥</span>';
    echo esc_html__('IMAP Konfigurasjon', 'skybug');
    echo '</h3>';
    echo '<div class="skybug-chart-controls">';
    echo '<button type="button" class="skybug-test-button" id="test-imap" data-nonce="' . esc_attr(wp_create_nonce('skybug_test_imap')) . '">' . esc_html__('Test IMAP', 'skybug') . '</button>';
    echo '</div>';
    echo '</div>';
    echo '<div class="skybug-chart-container" style="height: auto; padding: var(--spacing-6);">';
    echo '<p class="skybug-settings-description">' . esc_html__('Konfigurer IMAP for å motta e-post og automatisk opprette saker.', 'skybug') . '</p>';
    
    // IMAP enable checkbox
    echo '<div class="skybug-settings-field">';
    echo '<label class="skybug-settings-label">';
    $imap_enabled = get_option('skybug_imap_enabled', false);
    echo '<input type="checkbox" name="skybug_imap_enabled" value="1" ' . checked($imap_enabled, true, false) . ' style="margin-right: var(--spacing-2);" />';
    echo esc_html__('Aktiver IMAP mottak', 'skybug');
    echo '</label>';
    echo '<p class="skybug-field-description">' . esc_html__('Automatisk opprett saker fra innkommende e-post.', 'skybug') . '</p>';
    echo '</div>';
    
    $imap_fields = [
        'skybug_imap_host' => ['label' => __('IMAP Server', 'skybug'), 'type' => 'text', 'placeholder' => 'mail.smartesider.no', 'description' => __('IMAP server hostname eller IP-adresse.', 'skybug')],
        'skybug_imap_port' => ['label' => __('IMAP Port', 'skybug'), 'type' => 'number', 'default' => 993, 'description' => __('Standard: 993 (SSL) eller 143 (ingen kryptering).', 'skybug')],
        'skybug_imap_username' => ['label' => __('IMAP Brukernavn', 'skybug'), 'type' => 'text', 'placeholder' => 'bug@smartesider.no', 'description' => __('E-postadresse eller brukernavn for IMAP-tilgang.', 'skybug')],
        'skybug_imap_password' => ['label' => __('IMAP Passord', 'skybug'), 'type' => 'password', 'description' => __('E-post konto passord eller app-spesifikk passord.', 'skybug')],
        'skybug_imap_security' => ['label' => __('IMAP Sikkerhet', 'skybug'), 'type' => 'select', 'options' => ['none' => __('Ingen', 'skybug'), 'ssl' => 'SSL', 'tls' => 'TLS'], 'default' => 'ssl', 'description' => __('Anbefalt: SSL for port 993.', 'skybug')],
        'skybug_imap_folder' => ['label' => __('IMAP Mappe', 'skybug'), 'type' => 'text', 'default' => 'INBOX', 'description' => __('Mappe å overvåke for innkommende saker (standard: INBOX).', 'skybug')]
    ];
    
    echo '<div class="skybug-settings-grid">';
    foreach ($imap_fields as $field => $config) {
        echo '<div class="skybug-settings-field">';
        echo '<label class="skybug-settings-label">' . esc_html($config['label']) . '</label>';
        
        $value = get_option($field, isset($config['default']) ? $config['default'] : '');
        
        if ($config['type'] === 'select') {
            echo '<select name="' . $field . '" class="skybug-settings-input">';
            foreach ($config['options'] as $opt_value => $opt_label) {
                echo '<option value="' . esc_attr($opt_value) . '"' . selected($value, $opt_value, false) . '>' . esc_html($opt_label) . '</option>';
            }
            echo '</select>';
        } else {
            $input_type = $config['type'] === 'number' ? 'number' : ($config['type'] === 'password' ? 'password' : 'text');
            $extra_attrs = $config['type'] === 'number' ? ' min="1" max="65535"' : '';
            $placeholder = isset($config['placeholder']) ? ' placeholder="' . esc_attr($config['placeholder']) . '"' : '';
            echo '<input type="' . $input_type . '" name="' . $field . '" value="' . esc_attr($value) . '" class="skybug-settings-input"' . $extra_attrs . $placeholder . ' />';
        }
        
        if (isset($config['description'])) {
            echo '<p class="skybug-field-description">' . esc_html($config['description']) . '</p>';
        }
        
        echo '</div>';
    }
    echo '</div>';
    
    // Test result for IMAP
    echo '<div id="imap-test-result" class="skybug-test-result"></div>';
    
    echo '</div>';
    echo '</div>';
    
    echo '</div>'; // End settings grid
    if(SKYBUG_SETTINGS_DEBUG) error_log('[SkyBug][SETTINGS] END_GRID dt='.number_format(microtime(true)-$t0,5));
    if(SKYBUG_SETTINGS_DEBUG) error_log('[SkyBug][SETTINGS] BEFORE_SAVE_BUTTON dt='.number_format(microtime(true)-$t0,5));
    echo '<div class="skybug-quick-actions">';
    submit_button(__('Lagre alle innstillinger', 'skybug'), 'primary', 'submit', false, array('class' => 'skybug-quick-action'));
    echo '</div>';
    
    echo '</form>';
    
    // Status cards
    if(SKYBUG_SETTINGS_DEBUG) error_log('[SkyBug][SETTINGS] METRICS_START dt='.number_format(microtime(true)-$t0,5));
    echo '<div class="skybug-metrics-grid" style="margin-top: var(--spacing-8);">';
    
    // API Status card
    echo '<div class="skybug-metric-card">';
    echo '<div class="skybug-metric-header">';
    echo '<div class="skybug-metric-icon api">�</div>';
    echo '<h3 class="skybug-metric-title">' . esc_html__('API Status', 'skybug') . '</h3>';
    echo '</div>';
    echo '<div class="skybug-metric-content">';
    echo '<div class="skybug-metric-value">✅</div>';
    echo '<div class="skybug-metric-label">' . esc_html__('REST API Aktiv', 'skybug') . '</div>';
    echo '</div>';
    echo '</div>';
    
    // SMTP Status card
    echo '<div class="skybug-metric-card">';
    echo '<div class="skybug-metric-header">';
    echo '<div class="skybug-metric-icon smtp">📤</div>';
    echo '<h3 class="skybug-metric-title">' . esc_html__('SMTP Status', 'skybug') . '</h3>';
    echo '</div>';
    echo '<div class="skybug-metric-content">';
    $smtp_configured = !empty(get_option('skybug_smtp_host')) && !empty(get_option('skybug_smtp_username'));
    echo '<div class="skybug-metric-value">' . ($smtp_configured ? '✅' : '⚠️') . '</div>';
    echo '<div class="skybug-metric-label">' . ($smtp_configured ? esc_html__('Konfigurert', 'skybug') : esc_html__('Ikke konfigurert', 'skybug')) . '</div>';
    echo '</div>';
    echo '</div>';
    
    // IMAP Status card
    echo '<div class="skybug-metric-card">';
    echo '<div class="skybug-metric-header">';
    echo '<div class="skybug-metric-icon imap">📥</div>';
    echo '<h3 class="skybug-metric-title">' . esc_html__('IMAP Status', 'skybug') . '</h3>';
    echo '</div>';
    echo '<div class="skybug-metric-content">';
    $imap_enabled = get_option('skybug_imap_enabled', false);
    $imap_configured = !empty(get_option('skybug_imap_host')) && !empty(get_option('skybug_imap_username'));
    echo '<div class="skybug-metric-value">';
    if ($imap_enabled && $imap_configured) {
        echo '✅';
    } elseif ($imap_configured) {
        echo '⚠️';
    } else {
        echo '❌';
    }
    echo '</div>';
    echo '<div class="skybug-metric-label">';
    if ($imap_enabled && $imap_configured) {
        echo esc_html__('Aktiv', 'skybug');
    } elseif ($imap_configured) {
        echo esc_html__('Konfigurert', 'skybug');
    } else {
        echo esc_html__('Ikke konfigurert', 'skybug');
    }
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    // Webhook Status card  
    echo '<div class="skybug-metric-card">';
    echo '<div class="skybug-metric-header">';
    echo '<div class="skybug-metric-icon webhook">📡</div>';
    echo '<h3 class="skybug-metric-title">' . esc_html__('Webhooks', 'skybug') . '</h3>';
    echo '</div>';
    echo '<div class="skybug-metric-content">';
    $webhook_count = 0;
    $programs = get_posts(array('post_type' => 'skybug_program', 'numberposts' => -1));
    foreach ($programs as $program) {
        $webhook = get_post_meta($program->ID, '_skybug_webhook_url', true);
        if (!empty($webhook)) $webhook_count++;
    }
    echo '<div class="skybug-metric-value">' . $webhook_count . '</div>';
    echo '<div class="skybug-metric-label">' . esc_html__('Konfigurerte', 'skybug') . '</div>';
    echo '</div>';
    echo '</div>';
    
    echo '</div>'; // End status cards
    
    echo '</div>'; // End wrapper
    if(SKYBUG_SETTINGS_DEBUG) error_log('[SkyBug][SETTINGS] COMPLETE total_dt='.number_format(microtime(true)-$t0,5));
}

// Epostvarsel ved ny sak - nå med SMTP støtte
# q8r9s1t2 - Send epost ved ny offentliggjort sak med SMTP - se AI-learned/funksjonslogg.json
# slutt 7g8h9i0j

// Brukermanual side callback
# 8h9i0j1k - Render Brukermanual side - se AI-learned/funksjonslogg.json
function skybug_render_manual_page() {
    echo '<div class="wrap"><h1>' . esc_html__('Brukermanual – SkyBug','skybug') . '</h1>';
    echo '<p>' . esc_html__('SkyBug-pluginen lar deg holde oversikt over feil og ønskede nye funksjoner for dine programmer.','skybug') . '</p>';
    echo '<p>' . esc_html__('Du kan registrere','skybug') . ' <strong>' . esc_html__('Programmer','skybug') . '</strong> ' . esc_html__('i fanen Programmer – hvert program representerer et system/produkt du vil spore saker for.','skybug') . '</p>';
    echo '<p>' . esc_html__('Under','skybug') . ' <strong>' . esc_html__('Saker','skybug') . '</strong> ' . esc_html__('kan du se alle innrapporterte saker. Bruk undermenyene','skybug') . ' <em>' . esc_html__('Feilrapporter','skybug') . '</em> ' . esc_html__('og','skybug') . ' <em>' . esc_html__('Ønskede funksjoner','skybug') . '</em> ' . esc_html__('for å filtrere etter type.','skybug') . '</p>';
    echo '<p><strong>' . esc_html__('Statistikk','skybug') . '</strong>-' . esc_html__('siden viser deg nøkkeltall om registrerte programmer og saker.','skybug') . '</p>';
    echo '<p>' . esc_html__('For oppsett, gå til','skybug') . ' <strong>' . esc_html__('Innstillinger','skybug') . '</strong>.</p>';
    echo '<p>' . esc_html__('Denne brukermanualen vil oppdateres etterhvert som nye funksjoner legges til.','skybug') . '</p>';
    
    echo '<h2>' . esc_html__('API-integrasjon','skybug') . '</h2>';
    echo '<p>' . esc_html__('Hvert program får en unik API-nøkkel. Utviklere kan sende en HTTP POST forespørsel til','skybug') . ' <code>/wp-json/skybug/v1/report</code> ' . esc_html__('for å registrere en ny sak. Inkluder feltene','skybug') . ' <code>api_key</code>, <code>title</code>, <code>description</code>, <code>type</code> ' . esc_html__('i JSON-body. Hvis nøkkelen er gyldig, opprettes saken automatisk i SkyBug.','skybug') . '</p>';
    echo '<h3>' . esc_html__('Aktivering / Deaktivering','skybug') . '</h3>';
    echo '<p>' . esc_html__('Du kan når som helst deaktivere API for et enkelt program via avkrysningsboksen "Aktiver API" i meta-boksen. Når API er deaktivert vil eksterne kall med korrekt nøkkel få feilkode 403 (api_disabled). Nøkkelen beholdes og kan aktiveres igjen uten regenerering.','skybug') . '</p>';
    echo '<h3>' . esc_html__('Statusworkflow','skybug') . '</h3>';
    echo '<p>' . esc_html__('Saker kan være Åpen eller Lukket. Du endrer dette i publiseringsboksen via nedtrekkslisten Status. Lukking trigger webhook hvis konfigurert.','skybug') . '</p>';
    echo '<h3>' . esc_html__('Webhook ved lukking','skybug') . '</h3>';
    echo '<p>' . esc_html__('Når en sak går til Lukket sender systemet et JSON-kall til programets Webhook URL dersom satt. Payload inkluderer event=issue_closed, issue_id, issue_title, program_id og timestamp.','skybug') . '</p>';
    echo '<h3>' . esc_html__('Epostvarsling','skybug') . '</h3>';
    echo '<p>' . esc_html__('Sett en epostadresse under Innstillinger → Varslinger for å få varsel når en ny sak opprettes. Tom verdi betyr at det ikke sendes epost.','skybug') . '</p>';
    echo '<h3>' . esc_html__('Feilresponsformat','skybug') . '</h3>';
    echo '<p>' . esc_html__('Alle REST-feil returneres nå som JSON med nøkkel success=false og objekt error {code, message}. Ved suksess returneres success=true sammen med issue_id, issue_url, type og program_id.','skybug') . '</p>';
    echo '<h3>' . esc_html__('Korrelasjons-ID','skybug') . '</h3>';
    echo '<p>' . esc_html__('Hvert REST-kall får en unik correlation_id i responsen som kan brukes i feilsøk og loggsammenstilling.','skybug') . '</p>';
    echo '<h3>' . esc_html__('Webhook signering','skybug') . '</h3>';
    echo '<p>' . esc_html__('Webhook payload signeres med headeren X-SkyBug-Signature (format: sha256=...). Signaturen er HMAC SHA256 av rå JSON med den hemmelige nøkkelen som genereres per program. Verifiser ved å beregne lokal HMAC og sammenligne med konstant tids-sammenligning.','skybug') . '</p>';
    echo '<h3>' . esc_html__('API-logger','skybug') . '</h3>';
    echo '<p>' . esc_html__('Under undermenyen API-logger kan du se de siste hendelser (inntil 200 linjer). Filen roteres automatisk når den passerer ca. 1MB og arkiveres med tidsstempel.','skybug') . '</p>';
    echo '<p>' . esc_html__('Arkiverte filer ligger i samme mappe (AI-learned) med navn api_calls.log.YYYYMMDDHHMMSS.jsonl.','skybug') . '</p>';
    echo '<h3>' . esc_html__('Rate Limiting','skybug') . '</h3>';
    echo '<p>' . esc_html__('API-kall er begrenset. Standard er 60 kall per 10 minutter per API-nøkkel (kan justeres via filtre skybug_rate_limit_max og skybug_rate_limit_window). Ved overskridelse returneres kode rate_limited med felt retry_in (sekunder) og limit.','skybug') . '</p>';
    echo '<h3>' . esc_html__('Metrics endepunkt','skybug') . '</h3>';
    echo '<p>' . esc_html__('Endepunktet','skybug') . ' <code>/wp-json/skybug/v1/metrics</code> ' . esc_html__('returnerer summerte tall. Standard response inkluderer totaler for programmer og saker.','skybug') . '</p>';
    echo '<ul style="list-style:disc;margin-left:20px;">';
    echo '<li><code>?details=1</code> ' . esc_html__('utvider med fordeling per type (bug/feature) og top_programs (åpne/lukkede per mest aktive).','skybug') . '</li>';
    echo '<li><code>?fresh=1</code> ' . esc_html__('ignorerer cache og henter nye tall.','skybug') . '</li>';
    echo '<li><code>cached</code> ' . esc_html__('= true i respons angir at innholdet kom fra cache.','skybug') . '</li>';
    echo '<li><code>ttl</code> ' . esc_html__('viser gjeldende cache-levetid i sekunder. Standard 60.','skybug') . '</li>';
    echo '</ul>';
    echo '<p>' . esc_html__('Cache-levetid kan endres med filter','skybug') . ' <code>skybug_metrics_cache_ttl</code>. ' . esc_html__('Antall top_programs kan justeres via','skybug') . ' <code>skybug_metrics_top_programs_limit</code>.</p>';
    echo '<p>' . esc_html__('Du kan tømme metrics cache fra Diverse-siden (knapp) eller via WP-CLI.','skybug') . '</p>';
    echo '<p><strong>WP-CLI:</strong><br/><code>wp skybug metrics --details</code><br/><code>wp skybug metrics --fresh --details</code><br/><code>wp skybug flush-metrics</code></p>';
    
    echo '</div>';
}
# slutt 8h9i0j1k

// Diverse side callback
# 9i0j1k2l - Render Diverse side - se AI-learned/funksjonslogg.json
function skybug_render_misc_page() {
    echo '<div class="wrap"><h1>' . esc_html__('Diverse','skybug') . '</h1>';
    echo '<p>' . esc_html__('Denne seksjonen kan inneholde diverse informasjon eller verktøy.','skybug') . '</p>';
    if(current_user_can('manage_options')) {
        $flush_url = wp_nonce_url(admin_url('admin-post.php?action=skybug_flush_metrics'), 'skybug_flush_metrics');
        echo '<h2>' . esc_html__('Metrics Cache','skybug') . '</h2>';
        echo '<p>' . esc_html__('Metrics endepunktet benytter caching for ytelse. Du kan tømme cachen manuelt her.','skybug') . '</p>';
        echo '<p><a class="button" href="' . esc_url($flush_url) . '">' . esc_html__('Tøm metrics cache nå','skybug') . '</a></p>';
    }
    echo '</div>';
}
# slutt 9i0j1k2l

// Admin action for flushing metrics cache
add_action('admin_post_skybug_flush_metrics', function(){
    if(!current_user_can('manage_options')) { wp_die('Forbidden'); }
    if(!check_admin_referer('skybug_flush_metrics')) { wp_die('Nonce'); }
    // Fjern begge cache varianter
    delete_transient('skybug_metrics_cache_v2_b');
    delete_transient('skybug_metrics_cache_v2_d');
    wp_safe_redirect(add_query_arg('skybug_metrics_flushed','1', admin_url('admin.php?page=skybug-misc')));
    exit;
});

// Loggvisning side
function skybug_render_logs_page() {
    if(!current_user_can('manage_options')) { return; }
    
    echo '<div class="wrap skybug-logs-page">';
    echo '<h1>' . esc_html__('API-logger og Aktivitet','skybug') . '</h1>';
    
    // Get filter parameters
    $program_filter = isset($_GET['program']) ? intval($_GET['program']) : 0;
    $log_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'programs';
    
    // Tabs
    echo '<div class="skybug-tabs">';
    echo '<a href="' . admin_url('admin.php?page=skybug_logs&type=programs') . '" class="skybug-tab ' . ($log_type === 'programs' ? 'active' : '') . '">' . esc_html__('Per Program','skybug') . '</a>';
    echo '<a href="' . admin_url('admin.php?page=skybug_logs&type=activity') . '" class="skybug-tab ' . ($log_type === 'activity' ? 'active' : '') . '">' . esc_html__('Siste Aktivitet','skybug') . '</a>';
    echo '<a href="' . admin_url('admin.php?page=skybug_logs&type=api') . '" class="skybug-tab ' . ($log_type === 'api' ? 'active' : '') . '">' . esc_html__('API Logger','skybug') . '</a>';
    echo '</div>';
    
    // Filters
    echo '<div class="skybug-filters">';
    $programs = get_posts(['post_type' => 'skybug_program', 'numberposts' => -1]);
    if (!empty($programs)) {
        echo '<label for="program-filter">' . esc_html__('Filter program:', 'skybug') . ' </label>';
        echo '<select id="program-filter">';
        echo '<option value="0">' . esc_html__('Alle programmer','skybug') . '</option>';
        foreach ($programs as $program) {
            $selected = ($program_filter == $program->ID) ? 'selected' : '';
            echo '<option value="' . $program->ID . '" ' . $selected . '>' . esc_html(get_the_title($program->ID)) . '</option>';
        }
        echo '</select>';
    }
    echo '</div>';
    
    if ($log_type === 'api') {
        skybug_render_api_logs($program_filter);
    } elseif ($log_type === 'activity') {
        skybug_render_activity_logs($program_filter);
    } else { // Default to 'programs'
        skybug_render_program_logs($program_filter);
    }
    
    echo '</div>'; // .wrap
    
    // Add JavaScript for filters
    echo '<script>
    document.getElementById("program-filter").addEventListener("change", function() {
        const currentUrl = new URL(window.location);
        if (this.value === "0") {
            currentUrl.searchParams.delete("program");
        } else {
            currentUrl.searchParams.set("program", this.value);
        }
        window.location.href = currentUrl.toString();
    });
    </script>';
}

// Render program-specific logs
function skybug_render_program_logs($program_filter = 0) {
    echo '<div class="skybug-program-logs">';
    echo '<h2>' . esc_html__('Rapporter per Program','skybug') . '</h2>';
    
    $programs = get_posts([
        'post_type' => 'skybug_program', 
        'numberposts' => -1,
        'include' => $program_filter ? [$program_filter] : null
    ]);
    
    foreach ($programs as $program) {
        echo '<div class="skybug-program-log-card">';
        echo '<h3>' . esc_html(get_the_title($program->ID)) . '</h3>';
        
        // Get issues for this program
        $issues = get_posts([
            'post_type' => 'skybug_issue',
            'meta_query' => [
                [
                    'key' => '_skybug_program_id',
                    'value' => $program->ID,
                    'compare' => '='
                ]
            ],
            'numberposts' => 10,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        // Program stats
        $api_key = get_post_meta($program->ID, '_skybug_api_key', true);
        $api_enabled = get_post_meta($program->ID, '_skybug_api_enabled', true);
        $last_activity = get_post_meta($program->ID, '_skybug_last_activity', true);
        
        echo '<div class="skybug-program-stats">';
        echo '<span class="stat"><strong>' . esc_html__('API Status:', 'skybug') . '</strong> ';
        if ($api_key && $api_enabled === '1') {
            echo '<span class="status-active">✓ Aktiv</span>';
        } else {
            echo '<span class="status-inactive">✗ Inaktiv</span>';
        }
        echo '</span>';
        
        if ($last_activity) {
            $time_ago = human_time_diff($last_activity, current_time('timestamp'));
            echo '<span class="stat"><strong>' . esc_html__('Siste aktivitet:', 'skybug') . '</strong> ' . $time_ago . ' siden</span>';
        }
        
        $total_issues = count($issues);
        echo '<span class="stat"><strong>' . esc_html__('Totale issues:', 'skybug') . '</strong> ' . $total_issues . '</span>';
        
        // Get additional stats
        $open_issues = get_posts([
            'post_type' => 'skybug_issue',
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_skybug_program_id',
                    'value' => $program->ID,
                    'compare' => '='
                ]
            ],
            'numberposts' => -1
        ]);
        
        $closed_issues = get_posts([
            'post_type' => 'skybug_issue',
            'post_status' => ['skybug_closed', 'skybug_resolved'],
            'meta_query' => [
                [
                    'key' => '_skybug_program_id',
                    'value' => $program->ID,
                    'compare' => '='
                ]
            ],
            'numberposts' => -1
        ]);
        
        echo '<span class="stat"><strong>' . esc_html__('Åpne:', 'skybug') . '</strong> ' . count($open_issues) . '</span>';
        echo '<span class="stat"><strong>' . esc_html__('Lukkede:', 'skybug') . '</strong> ' . count($closed_issues) . '</span>';
        echo '</div>';
        
        // Recent issues
        if (!empty($issues)) {
            echo '<div class="skybug-recent-issues">';
            echo '<h4>' . esc_html__('Siste issues:','skybug') . '</h4>';
            foreach (array_slice($issues, 0, 5) as $issue) {
                $issue_types = wp_get_post_terms($issue->ID, 'skybug_type');
                $type = !empty($issue_types) ? $issue_types[0]->slug : 'unknown';
                $type_icon = $type === 'bug' ? '🐛' : ($type === 'feature' ? '✨' : '📋');
                
                echo '<div class="skybug-issue-item">';
                echo '<span class="issue-icon">' . $type_icon . '</span>';
                echo '<span class="issue-title">' . esc_html($issue->post_title) . '</span>';
                echo '<span class="issue-date">' . human_time_diff(strtotime($issue->post_date), current_time('timestamp')) . ' siden</span>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<p><em>' . esc_html__('Ingen issues funnet for dette programmet.','skybug') . '</em></p>';
        }
        
        echo '</div>'; // .skybug-program-log-card
    }
    
    echo '</div>'; // .skybug-program-logs
}

// Render API-specific logs
function skybug_render_api_logs($program_filter = 0) {
    echo '<div class="skybug-api-logs">';
    echo '<h2>' . esc_html__('API Logger','skybug') . '</h2>';
    
    $dir = SKYBUG_DIR . 'AI-learned';
    $file = $dir . '/api_calls.log.jsonl';
    
    if(!file_exists($file)) {
        echo '<p><em>' . esc_html__('Ingen API loggfil funnet ennå.','skybug') . '</em></p>';
        echo '</div>';
        return;
    }
    
    $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if(!$lines) { $lines = array(); }
    
    // Parse and filter logs
    $logs = [];
    foreach (array_reverse($lines) as $line) {
        $entry = json_decode($line, true);
        if ($entry) {
            // Filter by program if specified
            if ($program_filter && isset($entry['program_id']) && $entry['program_id'] != $program_filter) {
                continue;
            }
            $logs[] = $entry;
        }
        if (count($logs) >= 20) break;
    }
    
    if (empty($logs)) {
        echo '<p><em>' . esc_html__('Ingen API-logger funnet.','skybug') . '</em></p>';
        echo '</div>';
        return;
    }
    
    echo '<div class="skybug-api-log-table">';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>' . esc_html__('Tidspunkt','skybug') . '</th>';
    echo '<th>' . esc_html__('Event','skybug') . '</th>';
    echo '<th>' . esc_html__('Status','skybug') . '</th>';
    echo '<th>' . esc_html__('Program','skybug') . '</th>';
    echo '<th>' . esc_html__('Detaljer','skybug') . '</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($logs as $log) {
        echo '<tr>';
        echo '<td>' . esc_html($log['ts'] ?? 'N/A') . '</td>';
        echo '<td>' . esc_html($log['event'] ?? 'unknown') . '</td>';
        
        $result = $log['result'] ?? 'unknown';
        $status_class = $result === 'success' ? 'success' : ($result === 'error' ? 'error' : 'unknown');
        echo '<td><span class="status-' . $status_class . '">' . esc_html($result) . '</span></td>';
        
        $program_name = 'N/A';
        if (isset($log['program_id'])) {
            $program_title = get_the_title($log['program_id']);
            $program_name = $program_title ? $program_title : 'Program #' . $log['program_id'];
        }
        echo '<td>' . esc_html($program_name) . '</td>';
        
        $details = '';
        if (isset($log['code'])) {
            $details = $log['code'];
        }
        if (isset($log['issue_id'])) {
            $details .= ($details ? ', ' : '') . 'Issue #' . $log['issue_id'];
        }
        echo '<td>' . esc_html($details) . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    
    echo '</div>'; // .skybug-api-logs
}

// Render activity logs (recent issues across all programs)
function skybug_render_activity_logs($program_filter = 0) {
    echo '<div class="skybug-activity-logs">';
    echo '<h2>' . esc_html__('Siste Aktivitet','skybug') . '</h2>';
    
    $meta_query = [];
    if ($program_filter) {
        $meta_query = [
            [
                'key' => '_skybug_program_id',
                'value' => $program_filter,
                'compare' => '='
            ]
        ];
    }
    
    $recent_issues = get_posts([
        'post_type' => 'skybug_issue',
        'post_status' => 'any',
        'numberposts' => 20,
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_query' => $meta_query
    ]);
    
    if (empty($recent_issues)) {
        echo '<p><em>' . esc_html__('Ingen nylige aktiviteter funnet.','skybug') . '</em></p>';
        echo '</div>';
        return;
    }
    
    echo '<div class="skybug-activity-list">';
    foreach ($recent_issues as $issue) {
        $program_id = get_post_meta($issue->ID, '_skybug_program_id', true);
        $program_name = $program_id ? get_the_title($program_id) : __('Ukjent program', 'skybug');
        $issue_types = wp_get_post_terms($issue->ID, 'skybug_type');
        $type = !empty($issue_types) ? $issue_types[0]->slug : 'unknown';
        $type_icon = $type === 'bug' ? '🐛' : ($type === 'feature' ? '✨' : '📋');
        
        $status_class = '';
        if ($issue->post_status === 'skybug_closed') {
            $status_class = 'closed';
        } elseif ($issue->post_status === 'publish') {
            $status_class = 'open';
        }
        
        echo '<div class="skybug-activity-item ' . $status_class . '">';
        echo '<div class="activity-icon">' . $type_icon . '</div>';
        echo '<div class="activity-content">';
        echo '<div class="activity-title">';
        echo '<a href="' . admin_url('post.php?post='.$issue->ID.'&action=edit') . '">' . esc_html($issue->post_title) . '</a>';
        echo '<span class="activity-status">[' . esc_html($issue->post_status) . ']</span>';
        echo '</div>';
        echo '<div class="activity-meta">';
        echo '<span class="activity-program">' . esc_html($program_name) . '</span>';
        echo ' • ';
        echo '<span class="activity-time">' . human_time_diff(strtotime($issue->post_date), current_time('timestamp')) . ' siden</span>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    echo '</div>'; // .skybug-activity-list
    
    echo '</div>'; // .skybug-activity-logs
}

// Legg til meta-bokser for Program (API integrasjon)
add_action('add_meta_boxes', 'skybug_add_program_meta_boxes');

# d4c3b2a1 - Legg til meta-bokser for Program - se AI-learned/funksjonslogg.json
function skybug_add_program_meta_boxes() {
    add_meta_box(
        'skybug_program_api_meta',
        __('API-Integrasjon','skybug'),
        'skybug_render_program_api_metabox',
        'skybug_program',
        'normal',
        'high'
    );
}
# slutt d4c3b2a1

// Render Program API metabox
# 1122aabb - Render Program API metabox - se AI-learned/funksjonslogg.json
function skybug_render_program_api_metabox($post) {
    // Hent eksisterende verdier
    $api_key = get_post_meta($post->ID, '_skybug_api_key', true);
    $webhook = get_post_meta($post->ID, '_skybug_webhook_url', true);
    $api_enabled = get_post_meta($post->ID, '_skybug_api_enabled', true);
    $webhook_secret = get_post_meta($post->ID, '_skybug_webhook_secret', true);
    if ($api_enabled === '') { $api_enabled = '1'; }
    
    if (!$api_key) {
        echo '<p>' . esc_html__('Ingen API-nøkkel generert enda. En nøkkel vil bli opprettet når du lagrer programmet.', 'skybug') . '</p>';
    } else {
        echo '<p><strong>' . esc_html__('API-nøkkel:', 'skybug') . '</strong> <code>' . esc_html($api_key) . '</code></p>';
        echo '<p>' . esc_html__('Denne nøkkelen brukes for å godkjenne API-tilkoblinger for dette programmet.', 'skybug') . '</p>';
        
        $endpoint = home_url('/wp-json/skybug/v1/report');
        echo '<p><strong>' . esc_html__('Endpoint for bug-API:', 'skybug') . '</strong> <code>' . esc_url($endpoint) . '</code></p>';
    }
    
    echo '<p><label for="skybug_webhook_field"><strong>' . esc_html__('Webhook URL:', 'skybug') . '</strong></label><br/>';
    echo '<input type="url" id="skybug_webhook_field" name="skybug_webhook_field" value="' . esc_attr($webhook) . '" style="width:100%;" />';
    echo '<br/><em>' . esc_html__('(Valgfritt) URL som får en webhook varsling ved statusendringer.', 'skybug') . '</em></p>';

    if(!$webhook_secret) {
        echo '<p><em>' . esc_html__('Webhook hemmelig nøkkel genereres ved lagring hvis webhook er satt.', 'skybug') . '</em></p>';
    } else {
        echo '<p><strong>' . esc_html__('Webhook hemmelig nøkkel:', 'skybug') . '</strong> <code>' . esc_html(substr($webhook_secret,0,12)) . '...</code></p>';
        echo '<p><em>' . esc_html__('Brukes til å verifisere signatur header (X-SkyBug-Signature). Oppbevar sikkert.', 'skybug') . '</em></p>';
    }

    if($webhook) {
        $url = wp_nonce_url(admin_url('admin-post.php?action=skybug_test_webhook&program_id='.$post->ID), 'skybug_test_webhook_'.$post->ID);
        echo '<p><a class="button" href="' . esc_url($url) . '">' . esc_html__('Send test-webhook','skybug') . '</a></p>';
        if(isset($_GET['skybug_test_webhook'])) {
            if($_GET['skybug_test_webhook']==='1') {
                echo '<div class="notice notice-success"><p>' . esc_html__('Test-webhook sendt (sjekk mottaker system).','skybug') . '</p></div>';
            } elseif($_GET['skybug_test_webhook']==='0') {
                echo '<div class="notice notice-error"><p>' . esc_html__('Test-webhook feilet.','skybug') . '</p></div>';
            }
        }
    }
}
# slutt 1122aabb

// Lagre Program meta
add_action('save_post_skybug_program', 'skybug_save_program_meta');

# bbcceeff - Lagre Program meta - se AI-learned/funksjonslogg.json
function skybug_save_program_meta($post_id) {
    // Sjekk nonce og rettigheter
    if (!isset($_POST['skybug_program_api_nonce']) || 
        !wp_verify_nonce($_POST['skybug_program_api_nonce'], 'skybug_program_api_nonce')) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Unngå auto-save loop
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Lagre webhook URL
    if (isset($_POST['skybug_webhook_field'])) {
        $url = esc_url_raw($_POST['skybug_webhook_field']);
        update_post_meta($post_id, '_skybug_webhook_url', $url);
    }

    // Lagre API enable toggle
    $enabled = isset($_POST['skybug_api_enabled']) ? '1' : '0';
    update_post_meta($post_id, '_skybug_api_enabled', $enabled);

    // Generer webhook secret hvis ikke finnes og webhook URL er satt
    $existing_secret = get_post_meta($post_id, '_skybug_webhook_secret', true);
    $wb = get_post_meta($post_id, '_skybug_webhook_url', true);
    if(!$existing_secret && $wb) {
        try {
            $secret = bin2hex(random_bytes(24));
        } catch(Exception $e) {
            $secret = wp_generate_password(32,false,false);
        }
        update_post_meta($post_id, '_skybug_webhook_secret', $secret);
    }
    
    // Generer API-nøkkel hvis ikke finnes
    $existing = get_post_meta($post_id, '_skybug_api_key', true);
    if (!$existing) {
        $key = bin2hex(random_bytes(16));
        update_post_meta($post_id, '_skybug_api_key', $key);
        
        // Legg til admin notice for generert nøkkel
        add_filter('redirect_post_location', 'skybug_program_admin_notice');
    }
}
# slutt bbcceeff

// Admin notice for generert API-nøkkel
function skybug_program_admin_notice($location) {
    return add_query_arg('skybug_key_generated', 1, $location);
}

# e1f2g3h4 - Admin notice ved generering av API-nøkkel - se AI-learned/funksjonslogg.json
add_action('admin_notices', function(){
    if(isset($_GET['skybug_key_generated'])) {
        echo '<div class="updated notice"><p>' . esc_html__('En API-nøkkel ble opprettet for dette programmet.', 'skybug') . '</p></div>';
    }
});
# slutt e1f2g3h4

// REST API endepunkt
add_action('rest_api_init', 'skybug_register_api_routes');

# aabbccdd - Registrer REST API-ruter - se AI-learned/funksjonslogg.json
function skybug_register_api_routes() {
    register_rest_route('skybug/v1', '/report', array(
        'methods' => 'POST',
        'callback' => 'skybug_api_report_callback',
        'permission_callback' => '__return_true',
    ));
    // Metrics endpoint (cached 60s)
    register_rest_route('skybug/v1', '/metrics', array(
        'methods' => 'GET',
        'callback' => 'skybug_api_metrics_callback',
        'permission_callback' => '__return_true',
    ));
}
# slutt aabbccdd

// Metrics endpoint callback
if(!function_exists('skybug_api_metrics_callback')) {
    function skybug_api_metrics_callback($request){
        $details = (bool) $request->get_param('details');
        $fresh   = (bool) $request->get_param('fresh');
        $ttl     = (int) apply_filters('skybug_metrics_cache_ttl', 60);
        if($ttl < 5) { $ttl = 5; }
        $cache_key = 'skybug_metrics_cache_v2' . ($details ? '_d' : '_b');
        if(!$fresh) {
            $cached = get_transient($cache_key);
            if(is_array($cached)) {
                return skybug_rest_success(array_merge($cached, array('cached'=>true)));
            }
        }
        global $wpdb;
        $program_count = wp_count_posts('skybug_program');
        $issue_count   = wp_count_posts('skybug_issue');
        // Grunn tall
        $metrics = array(
            'program_total'=> isset($program_count->publish)? (int)$program_count->publish : 0,
            'issue_total'=> isset($issue_count->publish)? (int)$issue_count->publish : 0,
            'issue_closed'=> isset($issue_count->skybug_closed)? (int)$issue_count->skybug_closed : 0,
        );
        // Open / closed reell counting (WP_Query for open publish, closed custom status)
        $metrics['issue_open'] = (int) (new WP_Query(array('post_type'=>'skybug_issue','post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids')))->found_posts;
    $metrics['issue_closed_count'] = (int) (new WP_Query(array('post_type'=>'skybug_issue','post_status'=>array('skybug_closed','skybug_resolved'),'posts_per_page'=>1,'fields'=>'ids')))->found_posts;
        $metrics['last_issue_id'] = (int) $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_type='skybug_issue' ORDER BY ID DESC LIMIT 1");
        $metrics['last_program_id'] = (int) $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_type='skybug_program' ORDER BY ID DESC LIMIT 1");
        if($details) {
            // by_type counts
            $bug_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(p.ID) FROM {$wpdb->posts} p INNER JOIN {$wpdb->term_relationships} tr ON p.ID=tr.object_id INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id=tt.term_taxonomy_id INNER JOIN {$wpdb->terms} t ON tt.term_id=t.term_id WHERE p.post_type='skybug_issue' AND p.post_status IN ('publish','skybug_closed','skybug_resolved') AND tt.taxonomy='skybug_type' AND t.slug=%s","bug"));
            $feature_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(p.ID) FROM {$wpdb->posts} p INNER JOIN {$wpdb->term_relationships} tr ON p.ID=tr.object_id INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id=tt.term_taxonomy_id INNER JOIN {$wpdb->terms} t ON tt.term_id=t.term_id WHERE p.post_type='skybug_issue' AND p.post_status IN ('publish','skybug_closed','skybug_resolved') AND tt.taxonomy='skybug_type' AND t.slug=%s","feature"));
            $metrics['by_type'] = array('bug'=>$bug_count,'feature'=>$feature_count);
            // Top programs (open issues desc)
            $limit = (int) apply_filters('skybug_metrics_top_programs_limit', 5);
            if($limit < 1) { $limit = 1; }
            $top_sql = $wpdb->prepare("SELECT pm.meta_value as program_id, COUNT(i.ID) as open_issues FROM {$wpdb->posts} i INNER JOIN {$wpdb->postmeta} pm ON i.ID=pm.post_id WHERE i.post_type='skybug_issue' AND i.post_status='publish' AND pm.meta_key='_skybug_program_id' GROUP BY pm.meta_value ORDER BY open_issues DESC LIMIT %d", $limit);
            $rows = $wpdb->get_results($top_sql);
            $top = array();
            if($rows) {
                foreach($rows as $r) {
                    $prog_post = get_post((int)$r->program_id);
                    if($prog_post && $prog_post->post_type==='skybug_program') {
                        // closed count for this program
                        $closed_c = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(i2.ID) FROM {$wpdb->posts} i2 INNER JOIN {$wpdb->postmeta} pm2 ON i2.ID=pm2.post_id WHERE i2.post_type='skybug_issue' AND i2.post_status='skybug_closed' AND pm2.meta_key='_skybug_program_id' AND pm2.meta_value=%d", (int)$r->program_id));
                        $top[] = array(
                            'program_id'=>(int)$r->program_id,
                            'title'=>$prog_post->post_title,
                            'open_issues'=>(int)$r->open_issues,
                            'closed_issues'=>$closed_c
                        );
                    }
                }
            }
            $metrics['top_programs'] = $top;
        }
        $data = array('metrics'=>$metrics);
        set_transient($cache_key, $data, $ttl);
        return skybug_rest_success(array_merge($data, array('cached'=>false,'details'=>$details,'fresh'=>$fresh,'ttl'=>$ttl)));
    }
}

// Felles REST feil / suksess formatter
if(!function_exists('skybug_rest_error')) {
    function skybug_rest_error($code, $message, $http_status = 400, $extra = array()) {
        $cid = skybug_generate_correlation_id();
        $payload = array_merge(array(
            'success' => false,
            'error' => array(
                'code' => $code,
                'message' => $message
            ),
            'correlation_id' => $cid,
            'timestamp' => current_time('mysql', true)
        ), $extra);
        $response = new WP_REST_Response($payload, $http_status);
        $response->set_status($http_status);
        return $response;
    }
}

if(!function_exists('skybug_rest_success')) {
    function skybug_rest_success($data) {
        $cid = skybug_generate_correlation_id();
        $payload = array_merge(array(
            'success'=>true,
            'correlation_id'=>$cid,
            'timestamp'=> current_time('mysql', true)
        ), $data);
        return new WP_REST_Response($payload, 200);
    }
}

if(!function_exists('skybug_generate_correlation_id')) {
    function skybug_generate_correlation_id() {
        try {
            return bin2hex(random_bytes(8));
        } catch(Exception $e) {
            return substr(md5(uniqid((string)mt_rand(), true)),0,16);
        }
    }
}

if(!function_exists('skybug_rate_limit_check')) {
    function skybug_rate_limit_check($key) {
        $max = apply_filters('skybug_rate_limit_max', 60); // default 60 kall
        $window = apply_filters('skybug_rate_limit_window', 600); // 600s = 10 min
        $bucket_key = 'skybug_rl_' . md5($key);
        $data = get_transient($bucket_key);
        if(!is_array($data)) {
            $data = array('count'=>0,'start'=>time());
        }
        if(time() - $data['start'] > $window) {
            $data = array('count'=>0,'start'=>time());
        }
        $data['count']++;
        set_transient($bucket_key, $data, $window);
        if($data['count'] > $max) {
            return array(false, $max, ($window - (time() - $data['start'])));
        }
        return array(true, $max, ($window - (time() - $data['start'])));
    }
}

if(!function_exists('skybug_get_recent_api_errors')) {
    function skybug_get_recent_api_errors($limit = 5) {
        $dir = SKYBUG_DIR . 'AI-learned';
        $file = $dir . '/api_calls.log.jsonl';
        $errors = [];
        
        if (!file_exists($file)) {
            return $errors;
        }
        
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return $errors;
        }
        
        // Få de siste linjene først
        $lines = array_reverse($lines);
        
        foreach ($lines as $line) {
            $entry = json_decode($line, true);
            if ($entry && isset($entry['result']) && $entry['result'] === 'error') {
                $errors[] = [
                    'timestamp' => $entry['ts'] ?? '',
                    'code' => $entry['code'] ?? 'unknown',
                    'message' => skybug_get_error_message($entry['code'] ?? ''),
                    'program_id' => $entry['program_id'] ?? 0
                ];
                
                if (count($errors) >= $limit) {
                    break;
                }
            }
        }
        
        return $errors;
    }
}

if(!function_exists('skybug_get_error_message')) {
    function skybug_get_error_message($code) {
        $messages = [
            'missing_fields' => __('Mangler påkrevde felter', 'skybug'),
            'auth_failed' => __('Ugyldig API-nøkkel', 'skybug'),
            'api_disabled' => __('API deaktivert for program', 'skybug'),
            'rate_limited' => __('For mange API-kall', 'skybug'),
            'insert_failed' => __('Kunne ikke opprette sak', 'skybug'),
            'unknown_state' => __('Uventet feil', 'skybug')
        ];
        return $messages[$code] ?? __('Ukjent feil', 'skybug');
    }
}

if(!function_exists('skybug_log_api_call')) {
    function skybug_log_api_call($entry) {
        $dir = SKYBUG_DIR . 'AI-learned';
        if(!is_dir($dir)) { return; }
        $file = $dir . '/api_calls.log.jsonl';
        // Roter hvis >1MB
        if(file_exists($file) && filesize($file) > 1024*1024) {
            $archive = $dir . '/api_calls.log.' . gmdate('YmdHis') . '.jsonl';
            @rename($file, $archive);
        }
        $entry['ts'] = current_time('mysql', true);
        file_put_contents($file, json_encode($entry, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
    }
}

// Webhook dispatch helper
if(!function_exists('skybug_dispatch_webhook')) {
    function skybug_dispatch_webhook($program_id, $payload) {
        $webhook = get_post_meta($program_id, '_skybug_webhook_url', true);
        if(!$webhook) { return false; }
        $secret = get_post_meta($program_id, '_skybug_webhook_secret', true);
        $payload['timestamp'] = $payload['timestamp'] ?? time();
        $raw = wp_json_encode($payload);
        $headers = array('Content-Type'=>'application/json','X-SkyBug-Event'=> isset($payload['event']) ? $payload['event'] : 'unknown');
        $headers['X-SkyBug-Timestamp'] = (string)$payload['timestamp'];
        if($secret) {
            $signature = 'sha256=' . hash_hmac('sha256', $raw, $secret);
            $headers['X-SkyBug-Signature'] = $signature;
        }
        $res = wp_remote_post($webhook, array(
            'timeout' => 8,
            'headers' => $headers,
            'body' => $raw
        ));
        return !is_wp_error($res);
    }
}

// Admin post handler for test webhook
add_action('admin_post_skybug_test_webhook', function(){
    if(!current_user_can('manage_options')) { wp_die('Forbidden'); }
    if(!isset($_GET['program_id']) || !isset($_GET['_wpnonce'])) { wp_die('Bad request'); }
    $pid = (int) $_GET['program_id'];
    if(!wp_verify_nonce($_GET['_wpnonce'], 'skybug_test_webhook_'.$pid)) { wp_die('Nonce'); }
    $post = get_post($pid);
    if(!$post || $post->post_type !== 'skybug_program') { wp_die('Not found'); }
    $payload = array(
        'event' => 'test_webhook',
        'program_id' => $pid,
        'message' => 'Dette er en test av webhook',
        'timestamp' => time()
    );
    $ok = skybug_dispatch_webhook($pid, $payload);
    wp_safe_redirect(add_query_arg(array('skybug_test_webhook'=> $ok ? '1':'0'), get_edit_post_link($pid,'raw')));
    exit;
});

// REST API callback for bug rapport
# ddccbbaa - REST API callback motta bugrapport - se AI-learned/funksjonslogg.json
function skybug_api_report_callback($request) {
    $api_key = $request->get_param('api_key');
    $title = $request->get_param('title');
    $description = $request->get_param('description');
    $type = $request->get_param('type');

    $program = null;
    $response = null;
    $context = array('event'=>'report');

    // Valider felt
    if (empty($api_key) || empty($title) || empty($description) || empty($type)) {
        $context['result']='error';
        $context['code']='missing_fields';
        skybug_log_api_call($context);
        $response = skybug_rest_error('missing_fields', __('Mangler felt: api_key, title, description, type må alle være satt','skybug'), 400, array('missing'=>array('api_key','title','description','type')));
    } else {
        // Finn program
        $program = skybug_find_program_by_api_key($api_key);
        if (!$program) {
            $context['result']='error';
            $context['code']='auth_failed';
            skybug_log_api_call($context);
            $response = skybug_rest_error('auth_failed', __('Ugyldig API-nøkkel','skybug'), 403);
        }
    }

    if(!$response && $program) {
        $enabled = get_post_meta($program->ID, '_skybug_api_enabled', true);
        if ($enabled === '0') {
            $context['result']='error';
            $context['code']='api_disabled';
            $context['program_id']=$program->ID;
            skybug_log_api_call($context);
            $response = skybug_rest_error('api_disabled', __('API er deaktivert for dette programmet','skybug'), 403);
        }
    }

    if(!$response && $program) {
        list($allowed, $limit_max, $retry_in) = skybug_rate_limit_check($api_key);
        if(!$allowed) {
            $context['result']='error';
            $context['code']='rate_limited';
            $context['program_id']=$program->ID;
            $context['retry_in']=$retry_in;
            skybug_log_api_call($context);
            $response = skybug_rest_error('rate_limited', __('For mange kall – prøv igjen senere','skybug'), 429, array('retry_in'=>$retry_in,'limit'=>$limit_max));
        }
    }

    $post_id = 0;
    $term = '';
    if(!$response && $program) {
        $type_norm = strtolower($type);
        
        // Intelligent kategorisering basert på innkommende type
        if ($type_norm === 'bug') {
            $term = 'bug';
        } elseif ($type_norm === 'feature' || $type_norm === 'feature_request' || $type_norm === 'enhancement') {
            $term = 'feature';
        } else {
            // Alt annet går til "Undersøkes" for triage
            $term = 'undersokes';
        }
        
        $post_id = wp_insert_post(array(
            'post_title'   => sanitize_text_field($title),
            'post_content' => wp_kses_post($description),
            'post_type'    => 'skybug_issue',
            'post_status'  => 'publish',
            'post_author'  => 1
        ));
        if (is_wp_error($post_id)) {
            $context['result']='error';
            $context['code']='insert_failed';
            $context['program_id']=$program->ID;
            skybug_log_api_call($context);
            $response = skybug_rest_error('insert_failed', __('Kunne ikke opprette sak','skybug'), 500);
        }
    }

    if(!$response && $program && $post_id) {
        update_post_meta($post_id, '_skybug_program_id', $program->ID);
        wp_set_object_terms($post_id, $term, 'skybug_type');
        $context['result']='success';
        $context['program_id']=$program->ID;
        $context['issue_id']=$post_id;
        $context['type']=$term;
        skybug_log_api_call($context);
        $response = skybug_rest_success(array(
            'issue_id' => $post_id,
            'issue_url' => admin_url('post.php?post='.$post_id.'&action=edit'),
            'type' => $term,
            'program_id' => $program->ID
        ));
    }

    if(!$response) {
        // Fallback uventet
        $context['result']='error';
        $context['code']='unknown_state';
        skybug_log_api_call($context);
        $response = skybug_rest_error('unknown_state', __('Uventet tilstand','skybug'), 500);
    }
    return $response;
}
# slutt ddccbbaa

// Finn program etter API nøkkel
# j1k2l3m4 - Finn program etter API nøkkel - se AI-learned/funksjonslogg.json
function skybug_find_program_by_api_key($api_key) {
    if (empty($api_key)) { return null; }
    $programs = get_posts(array(
        'post_type' => 'skybug_program',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => '_skybug_api_key',
                'value' => sanitize_text_field($api_key)
            )
        )
    ));
    if (!$programs) { return null; }
    return $programs[0];
}
# slutt j1k2l3m4

// Tilpass kolonner i admin lister
add_filter('manage_skybug_issue_posts_columns', 'skybug_issue_columns');
add_action('manage_skybug_issue_posts_custom_column', 'skybug_issue_custom_column', 10, 2);

# f1a2b3c4 - Definer kolonner for skybug_issue liste - se AI-learned/funksjonslogg.json
function skybug_issue_columns($columns) {
    // Fjern standard kolonner
    unset($columns['date']);
    unset($columns['author']);
    
    // Legg til ticket management kolonner
    $columns['skybug_ticket_id'] = __('Ticket ID','skybug');
    $columns['skybug_status'] = __('Status','skybug');
    $columns['skybug_priority'] = __('Prioritet','skybug');
    $columns['skybug_program'] = __('Program','skybug');
    $columns['skybug_type'] = __('Type','skybug');
    $columns['skybug_reporter'] = __('Melder','skybug');
    $columns['skybug_assigned'] = __('Tildelt','skybug');
    $columns['skybug_last_activity'] = __('Sist oppdatert','skybug');
    $columns['skybug_actions'] = __('Handlinger','skybug');
    
    return $columns;
}
# slutt f1a2b3c4

# g2b3c4d5 - Render innhold i custom kolonner for skybug_issue - se AI-learned/funksjonslogg.json
function skybug_issue_custom_column($column, $post_id) {
    $post = get_post($post_id);
    
    switch($column) {
        case 'skybug_ticket_id':
            echo '<strong>#' . $post_id . '</strong>';
            break;
            
        case 'skybug_status':
            $status = $post->post_status;
            $status_config = [
                'publish' => ['label' => __('Ny/Åpen','skybug'), 'color' => '#dc3545', 'icon' => '🆕'],
                'skybug_in_progress' => ['label' => __('Under arbeid','skybug'), 'color' => '#fd7e14', 'icon' => '🔧'],
                'skybug_waiting' => ['label' => __('Venter på svar','skybug'), 'color' => '#ffc107', 'icon' => '⏳'],
                'skybug_resolved' => ['label' => __('Løst','skybug'), 'color' => '#20c997', 'icon' => '✅'],
                'skybug_closed' => ['label' => __('Lukket','skybug'), 'color' => '#6c757d', 'icon' => '🔒']
            ];
            
            $config = $status_config[$status] ?? $status_config['publish'];
            echo '<span class="skybug-status-badge" style="background: ' . $config['color'] . '; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold;">';
            echo $config['icon'] . ' ' . esc_html($config['label']);
            echo '</span>';
            break;
            
        case 'skybug_priority':
            $priority = get_post_meta($post_id, '_skybug_priority', true) ?: 'medium';
            $priority_config = [
                'low' => ['label' => __('Lav','skybug'), 'color' => '#28a745', 'icon' => '⬇️'],
                'medium' => ['label' => __('Middels','skybug'), 'color' => '#ffc107', 'icon' => '➡️'],
                'high' => ['label' => __('Høy','skybug'), 'color' => '#fd7e14', 'icon' => '⬆️'],
                'critical' => ['label' => __('Kritisk','skybug'), 'color' => '#dc3545', 'icon' => '🚨']
            ];
            
            $config = $priority_config[$priority] ?? $priority_config['medium'];
            echo '<span class="skybug-priority-badge" style="background: ' . $config['color'] . '; color: white; padding: 2px 6px; border-radius: 8px; font-size: 10px; font-weight: bold;">';
            echo $config['icon'] . ' ' . esc_html($config['label']);
            echo '</span>';
            break;
            
        case 'skybug_program':
            $prog_id = get_post_meta($post_id, '_skybug_program_id', true);
            if ($prog_id) {
                $prog_post = get_post($prog_id);
                if ($prog_post) {
                    echo '<strong>' . esc_html($prog_post->post_title) . '</strong>';
                } else {
                    echo '<em style="color: #dc3545;">' . esc_html__('Program slettet','skybug') . '</em>';
                }
            } else {
                echo '<em style="color: #6c757d;">' . esc_html__('Ikke tildelt','skybug') . '</em>';
            }
            break;
            
        case 'skybug_type':
            $terms = wp_get_post_terms($post_id, 'skybug_type');
            if(!empty($terms)) {
                $type_icons = ['bug' => '🐛', 'feature' => '💡', 'question' => '❓'];
                $icon = $type_icons[$terms[0]->slug] ?? '📝';
                echo '<span style="display: flex; align-items: center; gap: 4px;">';
                echo '<span>' . $icon . '</span>';
                echo '<span>' . esc_html($terms[0]->name) . '</span>';
                echo '</span>';
            } else {
                echo '<span style="color: #6c757d;">-</span>';
            }
            break;
            
        case 'skybug_reporter':
            $reporter_email = get_post_meta($post_id, '_skybug_reporter_email', true);
            $reporter_name = get_post_meta($post_id, '_skybug_reporter_name', true);
            
            if ($reporter_name) {
                echo '<div style="font-size: 12px;">';
                echo '<strong>' . esc_html($reporter_name) . '</strong>';
                if ($reporter_email) {
                    echo '<br><a href="mailto:' . esc_attr($reporter_email) . '" style="color: #007cba;">' . esc_html($reporter_email) . '</a>';
                }
                echo '</div>';
            } else {
                $author = get_user_by('id', $post->post_author);
                if ($author) {
                    echo '<div style="font-size: 12px;">';
                    echo '<strong>' . esc_html($author->display_name) . '</strong>';
                    echo '<br><span style="color: #6c757d;">(' . esc_html__('Intern','skybug') . ')</span>';
                    echo '</div>';
                }
            }
            break;
            
        case 'skybug_assigned':
            $assigned_user = get_post_meta($post_id, '_skybug_assigned_user', true);
            if ($assigned_user) {
                $user = get_user_by('id', $assigned_user);
                if ($user) {
                    echo '<strong>' . esc_html($user->display_name) . '</strong>';
                } else {
                    echo '<em style="color: #dc3545;">' . esc_html__('Bruker slettet','skybug') . '</em>';
                }
            } else {
                echo '<em style="color: #6c757d;">' . esc_html__('Ikke tildelt','skybug') . '</em>';
            }
            break;
            
        case 'skybug_last_activity':
            $last_activity = get_post_meta($post_id, '_skybug_last_activity', true);
            if ($last_activity) {
                $diff = human_time_diff($last_activity, current_time('timestamp'));
                echo '<span style="font-size: 12px; color: #6c757d;">' . sprintf(__('%s siden','skybug'), $diff) . '</span>';
            } else {
                $post_date = strtotime($post->post_date);
                $diff = human_time_diff($post_date, current_time('timestamp'));
                echo '<span style="font-size: 12px; color: #6c757d;">' . sprintf(__('%s siden','skybug'), $diff) . '</span>';
            }
            break;
            
        case 'skybug_actions':
            echo '<div class="skybug-quick-actions" style="display: flex; gap: 4px;">';
            
            // Quick status change buttons
            if ($post->post_status === 'publish') {
                echo '<button class="button button-small skybug-quick-status" data-post-id="' . $post_id . '" data-status="skybug_in_progress" style="background: #fd7e14; color: white; border: none; padding: 2px 6px; border-radius: 4px; cursor: pointer;" title="' . esc_attr__('Start arbeid','skybug') . '">🔧</button>';
            } elseif ($post->post_status === 'skybug_in_progress') {
                echo '<button class="button button-small skybug-quick-status" data-post-id="' . $post_id . '" data-status="skybug_resolved" style="background: #20c997; color: white; border: none; padding: 2px 6px; border-radius: 4px; cursor: pointer;" title="' . esc_attr__('Marker som løst','skybug') . '">✅</button>';
                echo '<button class="button button-small skybug-quick-status" data-post-id="' . $post_id . '" data-status="skybug_waiting" style="background: #ffc107; color: white; border: none; padding: 2px 6px; border-radius: 4px; cursor: pointer;" title="' . esc_attr__('Venter på svar','skybug') . '">⏳</button>';
            } elseif ($post->post_status === 'skybug_resolved') {
                echo '<button class="button button-small skybug-quick-status" data-post-id="' . $post_id . '" data-status="skybug_closed" style="background: #6c757d; color: white; border: none; padding: 2px 6px; border-radius: 4px; cursor: pointer;" title="' . esc_attr__('Lukk sak','skybug') . '">🔒</button>';
            }
            
            // Email button if reporter email exists
            $reporter_email = get_post_meta($post_id, '_skybug_reporter_email', true);
            if ($reporter_email) {
                echo '<button class="button button-small skybug-send-email" data-post-id="' . $post_id . '" data-email="' . esc_attr($reporter_email) . '" style="background: #007cba; color: white; border: none; padding: 2px 6px; border-radius: 4px; cursor: pointer;" title="' . esc_attr__('Send e-post til melder','skybug') . '">📧</button>';
            }
            
            echo '</div>';
            break;
    }
}
# slutt g2b3c4d5

// IMAP Status trafikklys på admin liste
add_action('admin_notices', function() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'edit-skybug_issue') {
        $imap_enabled = get_option('skybug_imap_enabled', false);
        $imap_configured = !empty(get_option('skybug_imap_host')) && !empty(get_option('skybug_imap_username'));
        
        // Determiner status
        if ($imap_enabled && $imap_configured) {
            $status = 'green';
            $status_text = __('IMAP Aktiv - Mottar automatisk nye saker via e-post', 'skybug');
            $icon = '🟢';
        } elseif ($imap_configured) {
            $status = 'yellow';  
            $status_text = __('IMAP Konfigurert men ikke aktivert', 'skybug');
            $icon = '🟡';
        } else {
            $status = 'red';
            $status_text = __('IMAP Ikke konfigurert - Nye saker må opprettes manuelt', 'skybug');
            $icon = '🔴';
        }
        
        echo '<div class="notice notice-info" style="display: flex; align-items: center; gap: 12px; margin: 16px 0; padding: 12px; border-left: 4px solid #007cba;">';
        echo '<div style="display: flex; align-items: center; gap: 8px;">';
        echo '<span style="font-size: 16px;">' . $icon . '</span>';
        echo '<strong>' . esc_html__('IMAP Status:', 'skybug') . '</strong>';
        echo '<span>' . esc_html($status_text) . '</span>';
        echo '</div>';
        
        if ($status !== 'green') {
            echo '<a href="' . admin_url('admin.php?page=skybug-settings') . '" class="button button-small">' . esc_html__('Konfigurer IMAP', 'skybug') . '</a>';
        }
        
        // Last email check status
        $last_check = get_option('skybug_last_imap_check', 0);
        if ($last_check && $imap_enabled) {
            $diff = human_time_diff($last_check, current_time('timestamp'));
            echo '<span style="margin-left: auto; font-size: 12px; color: #6c757d;">';
            echo sprintf(__('Sist sjekket: %s siden', 'skybug'), $diff);
            echo '</span>';
        }
        
        echo '</div>';
    }
});

// Quick actions AJAX handlers
add_action('wp_ajax_skybug_quick_status', 'skybug_handle_quick_status');
function skybug_handle_quick_status() {
    if(defined('DOING_AJAX') && DOING_AJAX){
        if(function_exists('apache_setenv')){ @apache_setenv('no-gzip', '1'); }
        @ini_set('zlib.output_compression','Off');
        nocache_headers();
    }
    // Nonce
    if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'skybug_quick_actions') ) {
        wp_send_json_error(['message'=>'nonce']);
    }
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    if(!$post_id || get_post_type($post_id) !== 'skybug_issue') {
        wp_send_json_error(['message'=>'post']);
    }
    if( ! current_user_can('edit_post', $post_id) ) {
        wp_send_json_error(['message'=>'perm']);
    }
    $status = sanitize_text_field($_POST['status'] ?? '');
    $allowed_statuses = ['publish', 'skybug_in_progress', 'skybug_waiting', 'skybug_resolved', 'skybug_closed'];
    if(!in_array($status, $allowed_statuses)) {
        wp_send_json_error(['message'=>'invalid']);
    }
    $res = wp_update_post([
        'ID'=>$post_id,
        'post_status'=>$status
    ], true);
    if(is_wp_error($res)) {
        wp_send_json_error(['message'=>'update']);
    }
    update_post_meta($post_id,'_skybug_last_activity', current_time('timestamp'));
    $status_labels = [
        'publish' => __('Ny/Åpen','skybug'),
        'skybug_in_progress' => __('Under arbeid','skybug'),
        'skybug_waiting' => __('Venter på svar','skybug'),
        'skybug_resolved' => __('Løst','skybug'),
        'skybug_closed' => __('Lukket','skybug')
    ];
    $comment = sprintf(__('Status endret til "%s" via hurtighandling', 'skybug'), $status_labels[$status]);
    skybug_add_internal_comment($post_id, $comment);
    update_post_meta($post_id,'_skybug_last_status_change_source','ajax');
    // Invalider metrics cache
    delete_transient('skybug_metrics_cache_v2_b');
    delete_transient('skybug_metrics_cache_v2_d');
    wp_send_json_success(['message'=>'ok','status'=>$status,'label'=>$status_labels[$status]]);
}

// Tilpass Program kolonner
add_filter('manage_skybug_program_posts_columns', 'skybug_program_columns');
add_action('manage_skybug_program_posts_custom_column', 'skybug_program_custom_column', 10, 2);

# h3c4d5e6 - Definer kolonner for skybug_program liste - se AI-learned/funksjonslogg.json
function skybug_program_columns($columns) {
    $columns['skybug_key'] = __('API-nøkkel','skybug');
    $columns['skybug_webhook'] = __('Webhook','skybug');
    return $columns;
}

// Moderne programliste side
if(!function_exists('skybug_render_programs_page')) {
function skybug_render_programs_page() {
    $programs = get_posts(array(
        'post_type' => 'skybug_program',
        'posts_per_page' => -1,
        'post_status' => array('publish')
    ));
    echo '<div class="wrap skybug-modern-page">';
    echo '<h1 class="skybug-page-title"><span class="skybug-title-icon">📦</span>' . esc_html__('Programmer','skybug') . ' <span class="skybug-title-count">(' . count($programs) . ')</span></h1>';
    echo '<div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;margin:12px 0 24px">';
    echo '<input type="text" id="skybug-program-search" placeholder="' . esc_attr__('Søk programmer...','skybug') . '" style="min-width:240px" />';
    
    // Program type filter
    $all_program_types = get_terms(array('taxonomy' => 'skybug_program_type', 'hide_empty' => false));
    if (!empty($all_program_types) && !is_wp_error($all_program_types)) {
        echo '<select id="skybug-program-type-filter" style="min-width:140px">';
        echo '<option value="">' . esc_html__('Alle typer','skybug') . '</option>';
        foreach ($all_program_types as $type) {
            $icon = get_term_meta($type->term_id, 'skybug_type_icon', true);
            echo '<option value="' . esc_attr($type->name) . '">' . esc_html($icon . ' ' . $type->name) . '</option>';
        }
        echo '</select>';
    }
    
    echo '<button class="button button-primary" onclick="window.location.href=\'' . admin_url('post-new.php?post_type=skybug_program') . '\'">➕ ' . esc_html__('Nytt program','skybug') . '</button>';
    
    // Templates dropdown
    echo '<div class="button-group" style="position:relative;display:inline-block">';
    echo '<button class="button" id="skybug-template-btn">📋 ' . esc_html__('Fra mal','skybug') . '</button>';
    echo '<div class="skybug-template-menu" style="display:none;position:absolute;top:100%;left:0;background:white;border:1px solid #ddd;border-radius:4px;box-shadow:0 2px 8px rgba(0,0,0,0.15);z-index:1000;min-width:200px">';
    
    $templates = array(
        'web' => array('name' => 'Web Application', 'type' => 'web', 'desc' => 'Standard web app med API integration'),
        'mobile' => array('name' => 'Mobile App', 'type' => 'mobile', 'desc' => 'iOS/Android applikasjon'),
        'api' => array('name' => 'REST API', 'type' => 'api', 'desc' => 'Backend API service'),
        'service' => array('name' => 'Microservice', 'type' => 'service', 'desc' => 'Backend microservice')
    );
    
    foreach ($templates as $key => $template) {
        echo '<a href="#" class="skybug-template-option" data-template="' . esc_attr($key) . '" style="display:block;padding:8px 12px;text-decoration:none;color:#333;border-bottom:1px solid #f0f0f0">';
        echo '<div style="font-weight:600">' . esc_html($template['name']) . '</div>';
        echo '<div style="font-size:12px;color:#666">' . esc_html($template['desc']) . '</div>';
        echo '</a>';
    }
    
    echo '</div>'; // template menu
    echo '</div>'; // button group
    
    echo '</div>';
    if(empty($programs)) {
        echo '<div class="skybug-empty-state-modern"><div class="skybug-empty-icon-large">📦</div><h2>' . esc_html__('Ingen programmer funnet','skybug') . '</h2><p>' . esc_html__('Opprett ditt første program for å komme i gang.','skybug') . '</p></div>';
        echo '</div>';
        return;
    }
    echo '<div class="skybug-program-grid" id="skybug-program-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(360px,1fr));gap:16px">';
    foreach($programs as $prog){
        $api_key = get_post_meta($prog->ID,'_skybug_api_key', true);
        $api_enabled = get_post_meta($prog->ID,'_skybug_api_enabled', true);
        $webhook = get_post_meta($prog->ID,'_skybug_webhook_url', true);
        $open_issues = (new WP_Query(array('post_type'=>'skybug_issue','post_status'=>'publish','meta_key'=>'_skybug_program_id','meta_value'=>$prog->ID,'fields'=>'ids','posts_per_page'=>1)))->found_posts;
        $closed_issues = (new WP_Query(array('post_type'=>'skybug_issue','post_status'=>array('skybug_closed','skybug_resolved'),'meta_key'=>'_skybug_program_id','meta_value'=>$prog->ID,'fields'=>'ids','posts_per_page'=>1)))->found_posts;
        $masked = $api_key ? esc_html(substr($api_key,0,4) . '••••' . substr($api_key,-4)) : __('(Ingen nøkkel)','skybug');
        $status_dot = $api_enabled==='0' ? 'background:#dc3545' : 'background:#28a745';
        $status_label = $api_enabled==='0' ? __('Deaktivert','skybug') : __('Aktiv','skybug');
        
        // Get program type info
        $program_types = wp_get_post_terms($prog->ID, 'skybug_program_type');
        $type_icon = '📦'; // Default icon
        $type_name = '';
        if (!empty($program_types) && !is_wp_error($program_types)) {
            $type_icon = get_term_meta($program_types[0]->term_id, 'skybug_type_icon', true) ?: '📦';
            $type_name = $program_types[0]->name;
        }
        
        // Get repository info
        $repo_url = get_post_meta($prog->ID, '_skybug_repo_url', true);
        $repo_type = '';
        $repo_name = '';
        if ($repo_url) {
            if (strpos($repo_url, 'github.com') !== false) {
                $repo_type = 'github';
                $repo_name = '🐱 GitHub';
            } elseif (strpos($repo_url, 'gitlab.com') !== false) {
                $repo_type = 'gitlab';
                $repo_name = '🦊 GitLab';
            } else {
                $repo_type = 'git';
                $repo_name = '📦 Git';
            }
        }
        
        echo '<div class="skybug-program-card" data-search="' . esc_attr(mb_strtolower($prog->post_title . ' ' . $type_name)) . '" data-type="' . esc_attr($type_name) . '" style="border:1px solid #e1e4e8;border-radius:12px;padding:16px;display:flex;flex-direction:column;gap:12px;position:relative;background:#fff">';
        
        // Program Image Section
        $thumbnail_id = get_post_thumbnail_id($prog->ID);
        $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'thumbnail') : '';
        
        echo '<div class="skybug-program-image-section" style="display:flex;align-items:center;gap:12px;padding-bottom:12px;border-bottom:1px solid #f1f3f5">';
        echo '<div class="skybug-program-image-container" style="position:relative;width:64px;height:64px;border-radius:8px;overflow:hidden;background:#f8f9fa;border:2px dashed #dee2e6;display:flex;align-items:center;justify-content:center">';
        
        if ($thumbnail_url) {
            echo '<img src="' . esc_url($thumbnail_url) . '" alt="' . esc_attr($prog->post_title) . '" style="width:100%;height:100%;object-fit:cover" />';
        } else {
            echo '<span style="font-size:24px;opacity:0.7">' . esc_html($type_icon) . '</span>';
        }
        
        // Program type badge overlay
        if ($type_name) {
            echo '<div style="position:absolute;top:-4px;right:-4px;background:#2196f3;color:white;font-size:10px;padding:2px 4px;border-radius:4px;line-height:1">' . esc_html($type_name) . '</div>';
        }
        
        echo '</div>';
        echo '<div style="display:flex;flex-direction:column;gap:4px">';
        echo '<input type="file" id="program-image-' . $prog->ID . '" accept="image/*" style="display:none" data-program-id="' . $prog->ID . '" />';
        echo '<button type="button" class="button button-small skybug-upload-image" data-program-id="' . $prog->ID . '">🖼️ ' . ($thumbnail_url ? esc_html__('Endre bilde','skybug') : esc_html__('Last opp bilde','skybug')) . '</button>';
        
        if ($thumbnail_url) {
            echo '<button type="button" class="button button-small skybug-remove-image" data-program-id="' . $prog->ID . '" style="color:#dc3545">❌ ' . esc_html__('Fjern bilde','skybug') . '</button>';
        }
        
        echo '</div>';
        echo '</div>'; // image section
        
        echo '<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px">';
        echo '<div style="flex:1 1 auto">';
        echo '<h2 style="margin:0 0 4px;font-size:18px;line-height:1.2"><a href="' . esc_url(get_edit_post_link($prog->ID)) . '">' . esc_html($prog->post_title) . '</a></h2>';
        echo '<div style="display:flex;align-items:center;gap:8px;font-size:12px;color:#555"><span style="width:10px;height:10px;border-radius:50%;' . $status_dot . '"></span><span>' . esc_html($status_label) . '</span>';
        if($webhook){ echo '<span>• 📡 ' . esc_html__('Webhook','skybug') . '</span>'; }
        if($repo_url){ echo '<span>• <a href="' . esc_url($repo_url) . '" target="_blank" style="color:#0073aa;text-decoration:none">' . esc_html($repo_name) . '</a></span>'; }
        echo '</div>';
        echo '</div>';
        echo '<div style="display:flex;flex-direction:column;gap:6px;align-items:flex-end">';
        echo '<button class="button" data-action="reveal-key" data-program-id="' . $prog->ID . '">🔐 ' . esc_html__('Nøkkel','skybug') . '</button>';
        echo '<button class="button" data-action="regenerate-key" data-program-id="' . $prog->ID . '">♻️ ' . esc_html__('Regenerer','skybug') . '</button>';
        if($webhook){ echo '<button class="button" data-action="test-webhook" data-program-id="' . $prog->ID . '">📡 ' . esc_html__('Test','skybug') . '</button>'; }
        if($repo_url){ echo '<button class="button" data-action="check-repo" data-program-id="' . $prog->ID . '">📝 ' . esc_html__('Commits','skybug') . '</button>'; }
        echo '</div>';
        echo '</div>'; // header row
        echo '<div style="display:flex;gap:12px;flex-wrap:wrap;font-size:12px">';
        echo '<div style="background:#f1f3f5;padding:6px 10px;border-radius:8px">🐛 ' . esc_html__('Åpne','skybug') . ': ' . (int)$open_issues . '</div>';
        echo '<div style="background:#f1f3f5;padding:6px 10px;border-radius:8px">✅ ' . esc_html__('Løst','skybug') . ': ' . (int)$closed_issues . '</div>';
        
        // Performance Metrics Toggle
        echo '<button class="skybug-metrics-toggle button button-small" data-program-id="' . $prog->ID . '" style="background:#e3f2fd;border:1px solid #2196f3;color:#1976d2">📊 ' . esc_html__('Metrics','skybug') . '</button>';
        
        // API Tester Toggle (only show if API is enabled)
        if ($api_enabled !== '0') {
            echo '<button class="skybug-api-tester-toggle button button-small" data-program-id="' . $prog->ID . '" style="background:#fff3e0;border:1px solid #ff9800;color:#f57c00">🔌 ' . esc_html__('API Test','skybug') . '</button>';
        }
        
        echo '</div>';
        
        // Performance Metrics Panel (Initially Hidden)
        echo '<div class="skybug-metrics-panel" id="metrics-' . $prog->ID . '" style="display:none;background:#f8f9fa;border:1px solid #e9ecef;border-radius:8px;padding:12px;margin-top:8px">';
        echo '<div class="skybug-metrics-loading" style="text-align:center;color:#6c757d">⏳ ' . esc_html__('Laster metrics...','skybug') . '</div>';
        echo '<div class="skybug-metrics-content" style="display:none">';
        echo '<div class="skybug-metrics-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:8px;margin-bottom:12px">';
        echo '<div class="metric-card" style="background:white;padding:8px;border-radius:6px;text-align:center">';
        echo '<div class="metric-value" data-metric="resolution_rate" style="font-size:18px;font-weight:bold;color:#28a745">-</div>';
        echo '<div class="metric-label" style="font-size:10px;color:#6c757d">' . esc_html__('Resolution Rate','skybug') . '</div>';
        echo '</div>';
        echo '<div class="metric-card" style="background:white;padding:8px;border-radius:6px;text-align:center">';
        echo '<div class="metric-value" data-metric="avg_resolution_time" style="font-size:18px;font-weight:bold;color:#17a2b8">-</div>';
        echo '<div class="metric-label" style="font-size:10px;color:#6c757d">' . esc_html__('Avg Days','skybug') . '</div>';
        echo '</div>';
        echo '<div class="metric-card" style="background:white;padding:8px;border-radius:6px;text-align:center">';
        echo '<div class="metric-value" data-metric="health_score" style="font-size:18px;font-weight:bold;color:#ffc107">-</div>';
        echo '<div class="metric-label" style="font-size:10px;color:#6c757d">' . esc_html__('Health Score','skybug') . '</div>';
        echo '</div>';
        echo '</div>'; // metrics grid
        echo '<div class="skybug-trend-chart" style="height:60px;background:white;border-radius:6px;padding:8px;position:relative;overflow:hidden">';
        echo '<canvas class="trend-canvas" data-program-id="' . $prog->ID . '" width="300" height="44"></canvas>';
        echo '</div>';
        echo '</div>'; // metrics content
        echo '</div>'; // metrics panel
        
        // API Tester Panel (Initially Hidden, only if API enabled)
        if ($api_enabled !== '0') {
            echo '<div class="skybug-api-tester-panel" id="api-tester-' . $prog->ID . '" style="display:none;background:#f8f9fa;border:1px solid #e9ecef;border-radius:8px;padding:12px;margin-top:8px">';
            echo '<div style="margin-bottom:12px">';
            echo '<label style="font-size:11px;font-weight:600;color:#495057">Test Endpoint:</label>';
            echo '<div style="display:flex;gap:8px;align-items:center;margin-top:4px">';
            echo '<select class="api-method-select" style="width:80px;font-size:11px">';
            echo '<option value="GET">GET</option>';
            echo '<option value="POST">POST</option>';
            echo '<option value="PUT">PUT</option>';
            echo '<option value="DELETE">DELETE</option>';
            echo '</select>';
            echo '<input type="text" class="api-endpoint-input" placeholder="/api/v1/test" style="flex:1;font-size:11px;padding:4px 8px" />';
            echo '<button class="button button-small skybug-test-api" data-program-id="' . $prog->ID . '" style="background:#ff9800;color:white;border:none">Test</button>';
            echo '</div>';
            echo '</div>';
            echo '<div class="api-response-container" style="background:white;border:1px solid #dee2e6;border-radius:4px;padding:8px;font-family:monospace;font-size:10px;max-height:120px;overflow-y:auto;color:#495057">';
            echo '<div class="api-response-placeholder" style="color:#6c757d;font-style:italic">Klikk "Test" for å teste et API endpoint</div>';
            echo '</div>';
            echo '</div>'; // api tester panel
        }
        
        echo '<div style="font-size:12px;color:#444;display:flex;align-items:center;gap:6px">';
        echo '<span style="font-weight:600">API:</span><code class="skybug-masked-key" data-full="' . esc_attr($api_key) . '">' . $masked . '</code>';
        echo '</div>';
        if($webhook){
            echo '<div style="font-size:11px;color:#555;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><span style="font-weight:600">Webhook:</span> ' . esc_html($webhook) . '</div>';
        }
        echo '<div class="skybug-program-feedback" style="min-height:16px;font-size:11px;color:#155724"></div>';
        echo '</div>'; // card
    }
    echo '</div>'; // grid
    echo '</div>';// end wrap
}
} // end if function exists guard
# slutt h3c4d5e6

# i4d5e6f7 - Render innhold i custom kolonner for skybug_program - se AI-learned/funksjonslogg.json
function skybug_program_custom_column($column, $post_id) {
    if($column == 'skybug_key') {
        $key = get_post_meta($post_id, '_skybug_api_key', true);
        if($key) {
            echo '<code>' . esc_html(substr($key,0,8)) . '...</code>';
        } else {
            echo '<em>' . esc_html__('(Ingen nøkkel)','skybug') . '</em>';
        }
    }
    if($column == 'skybug_webhook') {
        $url = get_post_meta($post_id, '_skybug_webhook_url', true);
        if($url) {
            echo '<a href="' . esc_url($url) . '" target="_blank">' . esc_html__('Webhook','skybug') . '</a>';
        } else {
            echo '<span style="color:#aaa;">-</span>';
        }
    }
}
# slutt i4d5e6f7

// Meta-boks for å velge program på en sak
# r1s2t3u4 - Registrer meta-boks for programvalg på skybug_issue - se AI-learned/funksjonslogg.json
add_action('add_meta_boxes', function(){
    add_meta_box('skybug_issue_program_select', __('Programtilknytning','skybug'), function($post){
        if($post->post_type !== 'skybug_issue') { return; }
        $current = get_post_meta($post->ID, '_skybug_program_id', true);
        $programs = get_posts(array('post_type'=>'skybug_program','numberposts'=>-1,'post_status'=>'publish'));
        echo '<label for="skybug_issue_program_field" class="screen-reader-text">' . esc_html__('Velg program','skybug') . '</label>';
        echo '<select name="skybug_issue_program_field" id="skybug_issue_program_field" style="width:100%">';
        echo '<option value="">' . esc_html__('(Ingen)','skybug') . '</option>';
        foreach($programs as $prog){
            echo '<option value="' . esc_attr($prog->ID) . '" ' . selected($current,$prog->ID,false) . '>' . esc_html($prog->post_title) . '</option>';
        }
        echo '</select>';
        wp_nonce_field('skybug_issue_program_nonce','skybug_issue_program_nonce');
    }, 'skybug_issue','side','default');
});
# slutt r1s2t3u4

// Lagring av programvalg for sak
# s2t3u4v5 - Lagre programtilknytning for sak - se AI-learned/funksjonslogg.json
add_action('save_post_skybug_issue', function($post_id){
    if(!isset($_POST['skybug_issue_program_nonce']) || !wp_verify_nonce($_POST['skybug_issue_program_nonce'],'skybug_issue_program_nonce')) { return; }
    if(!current_user_can('edit_post',$post_id)) { return; }
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
    if(isset($_POST['skybug_issue_program_field']) && $_POST['skybug_issue_program_field'] !== '') {
        update_post_meta($post_id,'_skybug_program_id', (int) $_POST['skybug_issue_program_field']);
    } elseif(isset($_POST['skybug_issue_program_field'])) {
        delete_post_meta($post_id,'_skybug_program_id');
    }
},20);
# slutt s2t3u4v5

// Moderne enkeltsak UI
add_action('edit_form_after_title', function($post){
    if($post->post_type !== 'skybug_issue') return;
    $priority = get_post_meta($post->ID,'_skybug_priority',true) ?: 'medium';
    $assigned_user = get_post_meta($post->ID,'_skybug_assigned_user',true);
    $reporter_name = get_post_meta($post->ID,'_skybug_reporter_name',true);
    $reporter_email = get_post_meta($post->ID,'_skybug_reporter_email',true);
    $last_activity = get_post_meta($post->ID,'_skybug_last_activity',true);
    $program_id = get_post_meta($post->ID,'_skybug_program_id',true);
    $program_title = $program_id ? get_the_title($program_id) : '';
    $terms = wp_get_post_terms($post->ID,'skybug_type');
    $type = !empty($terms) ? $terms[0]->slug : '';
    $internal_comments = skybug_get_internal_comments($post->ID);
    wp_nonce_field('skybug_issue_inline_meta','skybug_issue_inline_meta_nonce');
    echo '<div id="skybug-single-app" class="skybug-single-app" data-issue-id="'.esc_attr($post->ID).'" data-current-status="'.esc_attr($post->post_status).'" data-current-priority="'.esc_attr($priority).'" data-current-type="'.esc_attr($type).'" data-program-title="'.esc_attr($program_title).'" data-program-id="'.esc_attr($program_id).'">';
    echo '<div class="skybug-single-layout">';
    echo '<div class="skybug-single-main">';
    echo '<div class="skybug-single-section skybug-section-description">';
    echo '<div class="skybug-section-header"><h2>'.esc_html__('Beskrivelse','skybug').'</h2><div class="skybug-inline-status"></div></div>';
    echo '<div class="skybug-editor-toolbar" aria-label="'.esc_attr__('Editor verktøylinje','skybug').'">'
        .'<button type="button" data-command="bold" title="'.esc_attr__('Fet','skybug').'">B</button>'
        .'<button type="button" data-command="italic" title="'.esc_attr__('Kursiv','skybug').'"><em>I</em></button>'
        .'<button type="button" data-command="underline" title="'.esc_attr__('Understreket','skybug').'"><span style="text-decoration:underline">U</span></button>'
        .'<button type="button" data-command="insertUnorderedList" title="'.esc_attr__('Punktliste','skybug').'">• • •</button>'
        .'<button type="button" data-command="insertOrderedList" title="'.esc_attr__('Nummerliste','skybug').'">1.</button>'
        .'<button type="button" data-command="createLink" title="'.esc_attr__('Lenke','skybug').'">🔗</button>'
        .'<button type="button" data-command="removeFormat" title="'.esc_attr__('Fjern formatering','skybug').'">⨯</button>'
        .'<button type="button" data-command="undo" title="'.esc_attr__('Angre','skybug').'">↺</button>'
        .'<button type="button" data-command="redo" title="'.esc_attr__('Gjør om','skybug').'">↻</button>'
    .'</div>';
    // Hidden original editor (#content) will be kept in DOM by WordPress; we sync its value via JS. Display contenteditable div seeded with current content
    $initial_content = wp_kses_post($post->post_content);
    echo '<div id="skybug-rich-editor" class="skybug-rich-editor" contenteditable="true" aria-multiline="true" role="textbox">'.$initial_content.'</div>';
    echo '<p class="skybug-editor-hint" style="margin-top:8px;font-size:11px;color:#6c757d">'.esc_html__('Ctrl+Enter for å lagre (standard WordPress lagring), lenker åpnes i nytt vindu automatisk.','skybug').'</p>';
    echo '</div>';
    echo '<div class="skybug-single-section skybug-section-internal">';
    echo '<div class="skybug-section-header"><h2>'.esc_html__('Interne kommentarer','skybug').'</h2></div>';
    echo '<div id="skybug-internal-comments" class="skybug-internal-comments">';
    if($internal_comments){ foreach($internal_comments as $c){ $user = get_user_by('id',$c['user_id']); echo '<div class="skybug-comment"><div class="skybug-comment-meta">'.esc_html($user?$user->display_name:'User').' • '.esc_html(human_time_diff($c['timestamp'], current_time('timestamp'))).' '.__('siden','skybug').'</div><div class="skybug-comment-body">'.wp_kses_post($c['comment']).'</div></div>'; } } else { echo '<div class="skybug-comment-empty">'.esc_html__('Ingen interne kommentarer ennå.','skybug').'</div>'; }
    echo '</div>';
    echo '<div class="skybug-comment-new"><textarea id="skybug-new-internal" rows="3" placeholder="'.esc_attr__('Legg til intern kommentar...','skybug').'"></textarea><button type="button" class="button button-secondary" id="skybug-add-internal">'.esc_html__('Legg til','skybug').'</button></div>';
    echo '</div>';
    echo '</div>'; // main
    echo '<aside class="skybug-single-side">';
    echo '<div class="skybug-side-card"><h3>'.esc_html__('Status','skybug').'</h3><div class="skybug-status-buttons">';
    $statuses = array('publish'=>'Ny/Åpen','skybug_in_progress'=>'Under arbeid','skybug_waiting'=>'Venter','skybug_resolved'=>'Løst','skybug_closed'=>'Lukket');
    foreach($statuses as $k=>$lbl){ echo '<button type="button" class="skybug-status-btn" data-status="'.esc_attr($k).'">'.esc_html($lbl).'</button>'; }
    echo '</div></div>';
    echo '<div class="skybug-side-card"><h3>'.esc_html__('Detaljer','skybug').'</h3>';
    echo '<label>'.esc_html__('Prioritet','skybug').'<select id="skybug_priority_inline"><option value="low">'.esc_html__('Lav','skybug').'</option><option value="medium">'.esc_html__('Middels','skybug').'</option><option value="high">'.esc_html__('Høy','skybug').'</option><option value="critical">'.esc_html__('Kritisk','skybug').'</option></select></label>';
    echo '<label>'.esc_html__('Type','skybug').'<select id="skybug_type_inline"><option value="">'.esc_html__('(Velg)','skybug').'</option><option value="bug">'.esc_html__('Feilrapport','skybug').'</option><option value="feature">'.esc_html__('Ønsket funksjon','skybug').'</option></select></label>';
    echo '<div class="skybug-inline-program"><strong>'.esc_html__('Program','skybug').':</strong> '.($program_title?esc_html($program_title):'<em>'.esc_html__('Ingen','skybug').'</em>').'</div>';
    echo '<div class="skybug-last-activity">'.($last_activity ? sprintf(__('Sist aktivitet: %s siden','skybug'), human_time_diff($last_activity, current_time('timestamp'))) : '').'</div>';
    echo '</div>';
    echo '<div class="skybug-side-card"><h3>'.esc_html__('Melder','skybug').'</h3>'; if($reporter_name){ echo '<div>'.esc_html($reporter_name).'</div>'; } if($reporter_email){ echo '<div><a href="mailto:'.esc_attr($reporter_email).'">'.esc_html($reporter_email).'</a></div>'; } echo '</div>';
    // E-post panel integrert
    $imap_enabled = get_option('skybug_imap_enabled', false);
    $imap_configured = !empty(get_option('skybug_imap_host')) && !empty(get_option('skybug_imap_username'));
    $last_check = (int) get_option('skybug_last_imap_check', 0);
    $related_emails = get_post_meta($post->ID,'_skybug_related_emails', true);
    if(!is_array($related_emails)) $related_emails = array();
    echo '<div class="skybug-side-card skybug-email-card">';
    echo '<h3>'.esc_html__('E‑post','skybug').'</h3>';
    echo '<div class="skybug-email-status">';
    if($imap_enabled && $imap_configured){
        echo '<span class="skybug-imap-indicator active" title="'.esc_attr__('IMAP aktiv','skybug').'">🟢</span>';
        if($last_check){ echo '<small>'.sprintf(esc_html__('Sist sjekket %s siden','skybug'), human_time_diff($last_check, current_time('timestamp'))).'</small>'; }
    } elseif($imap_configured){
        echo '<span class="skybug-imap-indicator configured" title="'.esc_attr__('IMAP konfigurert men ikke aktiv','skybug').'">🟡</span>';
    } else {
        echo '<span class="skybug-imap-indicator inactive" title="'.esc_attr__('IMAP ikke konfigurert','skybug').'">🔴</span>';
    }
    echo '</div>';
    // Sist(e) e-poster
    echo '<div class="skybug-email-thread">';
    if($related_emails){
        $slice = array_slice($related_emails, -5);
        foreach($slice as $em){
            $dir = isset($em['direction']) ? $em['direction'] : 'in';
            $subject = isset($em['subject']) ? $em['subject'] : '';
            $from = isset($em['from']) ? $em['from'] : '';
            $date = isset($em['date']) ? (int)$em['date'] : 0;
            echo '<div class="skybug-email-item skybug-email-'.esc_attr($dir).'">';
            echo '<div class="skybug-email-meta">'.esc_html($from).' • '.esc_html($subject).' • '.($date?date('d.m H:i',$date):'').'</div>';
            if(!empty($em['snippet'])) echo '<div class="skybug-email-snippet">'.esc_html(wp_trim_words($em['snippet'],20,'…')).'</div>';
            echo '</div>';
        }
    } else {
        echo '<div class="skybug-email-empty">'.esc_html__('Ingen e‑poster knyttet til saken ennå.','skybug').'</div>';
    }
    echo '</div>';
    if($reporter_email){
        echo '<div class="skybug-email-reply">';
        echo '<input type="text" id="skybug-email-subject" placeholder="'.esc_attr__('Emne…','skybug').'" value="'.esc_attr(sprintf(__('Ang. sak #%d: %s','skybug'), $post->ID, $post->post_title)).'" />';
        echo '<textarea id="skybug-email-message" rows="4" placeholder="'.esc_attr__('Skriv melding…','skybug').'"></textarea>';
        echo '<button type="button" id="skybug-email-send" class="button button-secondary" data-post-id="'.esc_attr($post->ID).'">'.esc_html__('Send','skybug').'</button>';
        echo '<div class="skybug-email-feedback" style="display:none"></div>';
        echo '</div>';
    }
    echo '</div>';
    echo '</aside>';
    echo '</div></div>';
});

// Moderne single Program UI
add_action('edit_form_after_title', function($post){
    if($post->post_type !== 'skybug_program') return;
    $api_key = get_post_meta($post->ID,'_skybug_api_key', true);
    $api_enabled = get_post_meta($post->ID,'_skybug_api_enabled', true);
    $webhook = get_post_meta($post->ID,'_skybug_webhook_url', true);
    $webhook_secret = get_post_meta($post->ID,'_skybug_webhook_secret', true);
    $open_issues = (new WP_Query(array('post_type'=>'skybug_issue','post_status'=>'publish','meta_key'=>'_skybug_program_id','meta_value'=>$post->ID,'fields'=>'ids','posts_per_page'=>1)))->found_posts;
    $closed_issues = (new WP_Query(array('post_type'=>'skybug_issue','post_status'=>array('skybug_closed','skybug_resolved'),'meta_key'=>'_skybug_program_id','meta_value'=>$post->ID,'fields'=>'ids','posts_per_page'=>1)))->found_posts;
    echo '<div id="skybug-program-modern" class="skybug-program-modern" style="margin-top:12px">';
    echo '<div class="skybug-program-layout" style="display:grid;grid-template-columns:1fr 340px;gap:28px;align-items:start">';
    // Venstre kolonne
    echo '<div class="skybug-program-main" style="display:flex;flex-direction:column;gap:20px">';
    echo '<section class="skybug-card" style="background:#fff;border:1px solid #e1e4e8;border-radius:12px;padding:20px">';
    echo '<h2 style="margin:0 0 12px;font-size:18px">📦 ' . esc_html__('Programinformasjon','skybug') . '</h2>';
    echo '<label style="display:block;font-weight:600;margin-bottom:6px">' . esc_html__('Beskrivelse','skybug') . '</label>';
    echo '<div id="skybug-program-description" contenteditable="true" style="min-height:180px;border:1px solid #d0d7de;border-radius:8px;padding:12px;font-family:inherit;line-height:1.5;background:#fafbfc">' . wp_kses_post($post->post_content) . '</div>';
    // Bruk annet ID-navn for å unngå at WP Quicktags/WPEditor injiserer knapper i feil container
    echo '<input type="hidden" id="skybug_program_content_hidden" name="content" value="' . esc_attr($post->post_content) . '" />';
    echo '<p style="font-size:12px;color:#666;margin-top:8px">' . esc_html__('Endringer lagres når du klikker Oppdater.','skybug') . '</p>';
    echo '</section>';
    echo '<section class="skybug-card" style="background:#fff;border:1px solid #e1e4e8;border-radius:12px;padding:20px">';
    echo '<h2 style="margin:0 0 12px;font-size:18px">🐛 ' . esc_html__('Tilknyttede saker','skybug') . '</h2>';
    $issues = get_posts(array('post_type'=>'skybug_issue','numberposts'=>10,'meta_key'=>'_skybug_program_id','meta_value'=>$post->ID,'orderby'=>'ID','order'=>'DESC'));
    if($issues){
        echo '<ul style="margin:0;padding-left:18px;font-size:13px;max-height:260px;overflow:auto">';
        foreach($issues as $i){
            $st = $i->post_status;
            $icon = $st==='publish'?'🆕':($st==='skybug_in_progress'?'🔧':($st==='skybug_waiting'?'⏳':($st==='skybug_resolved'?'✅':'🔒')));
            echo '<li style="margin-bottom:4px"><a href="' . esc_url(get_edit_post_link($i->ID)) . '">' . $icon . ' #' . $i->ID . ' ' . esc_html(wp_trim_words($i->post_title,8)) . '</a></li>';
        }
        echo '</ul>';
    } else {
        echo '<p style="margin:0;color:#666">' . esc_html__('Ingen saker knyttet til dette programmet ennå.','skybug') . '</p>';
    }
    echo '</section>';
    echo '</div>'; // slutt venstre
    // Høyre kolonne
    echo '<aside class="skybug-program-side" style="display:flex;flex-direction:column;gap:20px">';
    echo '<section class="skybug-card" style="background:#fff;border:1px solid #e1e4e8;border-radius:12px;padding:18px">';
    echo '<h3 style="margin:0 0 10px;font-size:16px">🔐 ' . esc_html__('API & Nøkkel','skybug') . '</h3>';
    echo '<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">';
    $masked = $api_key ? esc_html(substr($api_key,0,4) . '••••' . substr($api_key,-4)) : __('(Ingen nøkkel)','skybug');
    echo '<code id="skybug-program-key" data-full="' . esc_attr($api_key) . '" style="font-size:12px;background:#f6f8fa;padding:4px 8px;border-radius:6px;display:inline-block">' . $masked . '</code>';
    echo '<button type="button" class="button" id="skybug-reveal-program-key">' . esc_html__('Vis','skybug') . '</button>';
    echo '<button type="button" class="button" id="skybug-regenerate-program-key">' . esc_html__('Regenerer','skybug') . '</button>';
    echo '</div>';
    echo '<label style="display:flex;align-items:center;gap:6px;font-size:13px;margin-bottom:10px"><input type="checkbox" name="skybug_api_enabled" value="1" ' . checked($api_enabled !== '0', true, false) . ' /> ' . esc_html__('Aktiver API','skybug') . '</label>';
    $endpoint = home_url('/wp-json/skybug/v1/report');
    echo '<p style="font-size:11px;margin:4px 0 0"><strong>POST</strong> <code>' . esc_html($endpoint) . '</code></p>';
    echo '<p style="font-size:11px;color:#666;margin:4px 0">api_key, title, description, type=bug|feature</p>';
    echo '<div id="skybug-program-key-feedback" style="font-size:11px;color:#155724;min-height:14px;margin-top:6px"></div>';
    echo '</section>';
    echo '<section class="skybug-card" style="background:#fff;border:1px solid #e1e4e8;border-radius:12px;padding:18px">';
    echo '<h3 style="margin:0 0 10px;font-size:16px">📡 ' . esc_html__('Webhook','skybug') . '</h3>';
    echo '<label style="display:block;font-weight:600;font-size:12px;margin-bottom:4px">' . esc_html__('Webhook URL','skybug') . '</label>';
    echo '<input type="url" name="skybug_webhook_field" value="' . esc_attr($webhook) . '" style="width:100%;margin-bottom:8px" />';
    if($webhook_secret){
        echo '<p style="font-size:11px;margin:4px 0"><strong>' . esc_html__('Hemmelig nøkkel','skybug') . ':</strong> <code>' . esc_html(substr($webhook_secret,0,10)) . '…</code></p>';
    }
    echo '<button type="button" class="button" id="skybug-test-program-webhook" ' . ($webhook ? '' : 'disabled') . '>' . esc_html__('Send test','skybug') . '</button>';
    echo '<div id="skybug-program-webhook-feedback" style="font-size:11px;color:#155724;min-height:14px;margin-top:6px"></div>';
    echo '</section>';
    echo '<section class="skybug-card" style="background:#fff;border:1px solid #e1e4e8;border-radius:12px;padding:18px">';
    echo '<h3 style="margin:0 0 10px;font-size:16px">📊 ' . esc_html__('Status','skybug') . '</h3>';
    echo '<div style="display:flex;flex-direction:column;gap:6px;font-size:12px">';
    echo '<div>🐛 ' . esc_html__('Åpne saker','skybug') . ': ' . (int)$open_issues . '</div>';
    echo '<div>✅ ' . esc_html__('Løste saker','skybug') . ': ' . (int)$closed_issues . '</div>';
    echo '</div>';
    echo '</section>';
    echo '</aside>';
    echo '</div>'; // grid layout
    echo '</div>'; // wrapper
    // Skjul klassisk editor visuelt
    echo '<style>#postdivrich,#post-status-info{display:none!important}</style>';
});

// Enqueue og lokaliser program JS
add_action('admin_enqueue_scripts', function(){
    $screen = get_current_screen();
    if(!$screen) return;
    // Program liste
    if($screen->id === 'edit-skybug_program'){
        wp_enqueue_style('skybug-programs-admin', SKYBUG_URL.'assets/css/programs-admin.css', array(), SKYBUG_VERSION);
        wp_enqueue_script('skybug-programs-admin', SKYBUG_URL.'assets/js/programs-admin.js', array('jquery'), SKYBUG_VERSION, true);
        wp_localize_script('skybug-programs-admin','skybugProgramsL10n', array(
            'showKey' => __('Nøkkel','skybug'),
            'hideKey' => __('Skjul','skybug'),
            'confirmRegen' => __('Regenerere nøkkel? Dette oppdaterer API-tilgang umiddelbart.','skybug'),
            'regenOk' => __('Ny nøkkel generert','skybug'),
            'regenFail' => __('Feil ved regenerering','skybug'),
            'regenerate' => __('Regenerer','skybug'),
            'testSent' => __('Test sendt','skybug'),
            'testFail' => __('Test feilet','skybug'),
            'testBtn' => __('Test','skybug'),
            'metrics' => __('Metrics','skybug'),
            'hideMetrics' => __('Skjul Metrics','skybug'),
            'uploadImage' => __('Last opp bilde','skybug'),
            'changeImage' => __('Endre bilde','skybug'),
            'removeImage' => __('Fjern bilde','skybug'),
            'confirmRemoveImage' => __('Fjerne dette bildet?','skybug'),
            'commits' => __('Commits','skybug'),
            'apiTest' => __('API Test','skybug'),
            'hideApiTest' => __('Skjul API Test','skybug'),
            'nonce' => wp_create_nonce('skybug_admin_nonce')
        ));
    }
    // Single program
    if($screen->id === 'skybug_program'){
        wp_enqueue_style('skybug-programs-admin', SKYBUG_URL.'assets/css/programs-admin.css', array(), SKYBUG_VERSION);
        wp_enqueue_script('skybug-program-single', SKYBUG_URL.'assets/js/program-single.js', array('jquery'), SKYBUG_VERSION, true);
        global $post;
        if($post && $post->post_type === 'skybug_program'){
            $api_key = get_post_meta($post->ID,'_skybug_api_key', true);
            wp_localize_script('skybug-program-single','skybugProgramSingle', array(
                'programId' => (int)$post->ID,
                'nonce' => wp_create_nonce('skybug_program_actions'),
                'showKey' => __('Vis','skybug'),
                'hideKey' => __('Skjul','skybug'),
                'confirmRegen' => __('Regenerere nøkkel? Dette oppdaterer API-tilgang umiddelbart.','skybug'),
                'regenOk' => __('Ny nøkkel generert','skybug'),
                'regenFail' => __('Feil ved regenerering','skybug'),
                'regenerate' => __('Regenerer','skybug'),
                'testSent' => __('Test sendt','skybug'),
                'testFail' => __('Test feilet','skybug'),
                'testBtn' => __('Send test','skybug')
            ));
        }
    }
});

add_action('admin_enqueue_scripts', function(){
    $screen = get_current_screen();
    if(!$screen || $screen->id !== 'skybug_issue') return;
    wp_enqueue_style('skybug-issue-modern', SKYBUG_URL.'assets/css/issue-modern.css', array(), SKYBUG_VERSION);
    wp_enqueue_style('skybug-issue-extras', SKYBUG_URL.'assets/css/issue-admin-extras.css', array('skybug-issue-modern'), SKYBUG_VERSION);
    wp_enqueue_script('skybug-issue-modern', SKYBUG_URL.'assets/js/issue-modern.js', array('jquery'), SKYBUG_VERSION, true);
    wp_localize_script('skybug-issue-modern','skybugIssueModern', array(
        'ajaxUrl'=>admin_url('admin-ajax.php'),
        'nonce'=>wp_create_nonce('skybug_quick_actions'),
        'commentsNonce'=>wp_create_nonce('skybug_internal_comment'),
        'emailNonce'=>wp_create_nonce('skybug_send_email'),
        'i18n'=>array(
            'saving'=>__('Lagrer...','skybug'),
            'added'=>__('Lagt til','skybug'),
            'error'=>__('Feil','skybug'),
            'sending'=>__('Sender...','skybug'),
            'sent'=>__('Sendt','skybug')
        )
    ));
});

add_action('wp_ajax_skybug_add_internal_comment', function(){
    if(!current_user_can('edit_posts')) wp_die(json_encode(['success'=>false,'message'=>'perm']));
    if(!wp_verify_nonce($_POST['nonce'] ?? '', 'skybug_internal_comment')) wp_die(json_encode(['success'=>false,'message'=>'nonce']));
    $post_id = intval($_POST['post_id'] ?? 0);
    $comment = wp_kses_post(wp_unslash($_POST['comment'] ?? ''));
    if(!$post_id || !$comment) wp_die(json_encode(['success'=>false,'message'=>'missing']));
    skybug_add_internal_comment($post_id, $comment);
    $last = end( (array) skybug_get_internal_comments($post_id) );
    $user = get_user_by('id', $last['user_id']);
    $html = '<div class="skybug-comment"><div class="skybug-comment-meta">'.esc_html($user?$user->display_name:'User').' • '.esc_html(__('nå','skybug')).'</div><div class="skybug-comment-body">'.wp_kses_post($last['comment']).'</div></div>';
    wp_die(json_encode(['success'=>true,'html'=>$html]));
});

// Send email to reporter via inline panel
add_action('wp_ajax_skybug_send_email', function(){
    if(!current_user_can('edit_posts')) wp_die(json_encode(['success'=>false,'message'=>'perm']));
    if(!wp_verify_nonce($_POST['nonce'] ?? '', 'skybug_send_email')) wp_die(json_encode(['success'=>false,'message'=>'nonce']));
    $post_id = intval($_POST['post_id'] ?? 0);
    $subject = sanitize_text_field(wp_unslash($_POST['subject'] ?? ''));
    $message = wp_kses_post(wp_unslash($_POST['message'] ?? ''));
    if(!$post_id || !$subject || !$message) wp_die(json_encode(['success'=>false,'message'=>'missing']));
    $reporter_email = get_post_meta($post_id,'_skybug_reporter_email',true);
    if(!$reporter_email) wp_die(json_encode(['success'=>false,'message'=>'noemail']));
    // Basic headers
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $sent = wp_mail($reporter_email, $subject, wpautop($message), $headers);
    if($sent){
        // Optionally store outbound email meta for thread
        $emails = get_post_meta($post_id,'_skybug_related_emails', true); if(!is_array($emails)) $emails = array();
        $emails[] = array(
            'direction'=>'out',
            'subject'=>$subject,
            'from'=> get_bloginfo('name').' <'.get_option('admin_email').'>',
            'date'=> current_time('timestamp'),
            'snippet'=> wp_strip_all_tags(wp_trim_words($message,25,'…'))
        );
        update_post_meta($post_id,'_skybug_related_emails', $emails);
        wp_die(json_encode(['success'=>true]));
    }
    wp_die(json_encode(['success'=>false,'message'=>'sendfail']));
});

// Shortcode for å vise åpne saker gruppert per program
# t3u4v5w6 - Shortcode [skybug-dashboard] - se AI-learned/funksjonslogg.json
add_shortcode('skybug-dashboard', function($atts){
    $atts = shortcode_atts(array(), $atts, 'skybug-dashboard');
    // Hent alle programmer
    $programs = get_posts(array('post_type'=>'skybug_program','numberposts'=>-1,'post_status'=>'publish'));
    if(empty($programs)) { return '<div class="skybug-list"><em>' . esc_html__('Ingen programmer tilgjengelig.','skybug') . '</em></div>'; }
    ob_start();
    echo '<div class="skybug-list">';
    foreach($programs as $prog){
        // Hent åpne saker for program (utelat lukket)
        $issues = get_posts(array(
            'post_type'=>'skybug_issue',
            'numberposts'=>-1,
            'post_status'=>'publish',
            'meta_query'=>array(
                array('key'=>'_skybug_program_id','value'=>$prog->ID)
            )
        ));
        // Filtrer ut lukkede manuelt hvis status felt endres senere
        $issues = array_filter($issues, function($p){ return $p->post_status !== 'skybug_closed'; });
        echo '<div class="skybug-program-block">';
        echo '<h3>' . esc_html($prog->post_title) . '</h3>';
        if(empty($issues)) {
            echo '<p><em>' . esc_html__('Ingen åpne saker.','skybug') . '</em></p>';
        } else {
            echo '<ul>';
            foreach($issues as $issue){
                $terms = wp_get_post_terms($issue->ID,'skybug_type');
                $type_label = !empty($terms) ? $terms[0]->name : '';
                echo '<li>' . esc_html($issue->post_title) . ' <span class="type">[' . esc_html($type_label) . ']</span></li>';
            }
            echo '</ul>';
        }
        echo '</div>';
    }
    echo '</div>';
    return ob_get_clean();
});
# slutt t3u4v5w6

// WP-CLI kommandoer
if(defined('WP_CLI') && WP_CLI) {
    class SkyBug_CLI_Command {
        public function metrics($args, $assoc_args) {
            $details = isset($assoc_args['details']);
            $fresh = isset($assoc_args['fresh']);
            $req = new WP_REST_Request('GET', '/skybug/v1/metrics');
            if($details) { $req->set_param('details', 1); }
            if($fresh) { $req->set_param('fresh', 1); }
            $res = skybug_api_metrics_callback($req);
            $data = $res instanceof WP_REST_Response ? $res->get_data() : $res;
            WP_CLI::line(json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        }
        public function flush_metrics($args, $assoc_args) {
            delete_transient('skybug_metrics_cache_v2_b');
            delete_transient('skybug_metrics_cache_v2_d');
            WP_CLI::success('Metrics cache tømt.');
        }
    }
    
    // AJAX handler for program image upload
    add_action('wp_ajax_skybug_upload_program_image', function() {
        check_ajax_referer('skybug_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $program_id = intval($_POST['program_id']);
        if (!$program_id) {
            wp_send_json_error('Invalid program ID');
        }
        
        // Verify program exists
        $program = get_post($program_id);
        if (!$program || $program->post_type !== 'skybug_program') {
            wp_send_json_error('Program not found');
        }
        
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        $attachment_id = media_handle_upload('image', $program_id);
        
        if (is_wp_error($attachment_id)) {
            wp_send_json_error($attachment_id->get_error_message());
        }
        
        // Set as featured image
        set_post_thumbnail($program_id, $attachment_id);
        
        // Get image data
        $image_url = wp_get_attachment_image_url($attachment_id, 'medium');
        $image_thumb = wp_get_attachment_image_url($attachment_id, 'thumbnail');
        
        wp_send_json_success([
            'attachment_id' => $attachment_id,
            'image_url' => $image_url,
            'thumbnail' => $image_thumb,
            'message' => 'Bilde lastet opp'
        ]);
    });
    
    // AJAX handler for removing program image
    add_action('wp_ajax_skybug_remove_program_image', function() {
        check_ajax_referer('skybug_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $program_id = intval($_POST['program_id']);
        if (!$program_id) {
            wp_send_json_error('Invalid program ID');
        }
        
        // Remove featured image
        delete_post_thumbnail($program_id);
        
        wp_send_json_success(['message' => 'Bilde fjernet']);
    });
    
    // AJAX handler for program performance metrics
    add_action('wp_ajax_skybug_get_program_metrics', function() {
        check_ajax_referer('skybug_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $program_id = intval($_POST['program_id']);
        if (!$program_id) {
            wp_send_json_error('Invalid program ID');
        }
        
        global $wpdb;
        
        // Get all issues for this program
        $issues = $wpdb->get_results($wpdb->prepare("
            SELECT p.ID, p.post_status, p.post_date, pm_created.meta_value as created_date, pm_resolved.meta_value as resolved_date
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm_prog ON (p.ID = pm_prog.post_id AND pm_prog.meta_key = '_skybug_program_id')
            LEFT JOIN {$wpdb->postmeta} pm_created ON (p.ID = pm_created.post_id AND pm_created.meta_key = '_skybug_created_date')
            LEFT JOIN {$wpdb->postmeta} pm_resolved ON (p.ID = pm_resolved.post_id AND pm_resolved.meta_key = '_skybug_resolved_date')
            WHERE p.post_type = 'skybug_issue' 
            AND pm_prog.meta_value = %d
            AND p.post_status IN ('publish', 'skybug_in_progress', 'skybug_waiting', 'skybug_resolved', 'skybug_closed')
            ORDER BY p.post_date DESC
        ", $program_id));
        
        // Calculate metrics
        $total_issues = count($issues);
        $open_issues = 0;
        $resolved_issues = 0;
        $resolution_times = [];
        $activity_by_month = [];
        
        foreach ($issues as $issue) {
            if (in_array($issue->post_status, ['skybug_resolved', 'skybug_closed'])) {
                $resolved_issues++;
                
                // Calculate resolution time if both dates exist
                $created = $issue->created_date ?: $issue->post_date;
                $resolved = $issue->resolved_date;
                
                if ($resolved && $created) {
                    $diff = strtotime($resolved) - strtotime($created);
                    if ($diff > 0) {
                        $resolution_times[] = $diff / 86400; // Convert to days
                    }
                }
            } else {
                $open_issues++;
            }
            
            // Activity by month
            $month = date('Y-m', strtotime($issue->post_date));
            $activity_by_month[$month] = ($activity_by_month[$month] ?? 0) + 1;
        }
        
        // Calculate average resolution time
        $avg_resolution_time = !empty($resolution_times) ? array_sum($resolution_times) / count($resolution_times) : 0;
        
        // Bug rate trend (issues per month for last 6 months)
        $bug_rate_trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-{$i} months"));
            $bug_rate_trend[] = [
                'month' => date('M Y', strtotime($month . '-01')),
                'count' => $activity_by_month[$month] ?? 0
            ];
        }
        
        // Health score calculation (0-100)
        $health_score = 100;
        if ($total_issues > 0) {
            $open_ratio = $open_issues / $total_issues;
            $health_score -= ($open_ratio * 60); // Penalty for open issues
            
            if ($avg_resolution_time > 7) {
                $health_score -= min(30, ($avg_resolution_time - 7) * 2); // Penalty for slow resolution
            }
            
            // Recent activity bonus
            $recent_month = date('Y-m');
            $recent_activity = $activity_by_month[$recent_month] ?? 0;
            if ($recent_activity === 0 && $open_issues > 0) {
                $health_score -= 20; // Penalty for inactive programs with open issues
            }
        }
        
        $health_score = max(0, min(100, $health_score));
        
        wp_send_json_success([
            'total_issues' => $total_issues,
            'open_issues' => $open_issues,
            'resolved_issues' => $resolved_issues,
            'resolution_rate' => $total_issues > 0 ? round(($resolved_issues / $total_issues) * 100, 1) : 0,
            'avg_resolution_time' => round($avg_resolution_time, 1),
            'health_score' => round($health_score),
            'bug_rate_trend' => $bug_rate_trend,
            'activity_heatmap' => array_slice($activity_by_month, -12, 12, true) // Last 12 months
        ]);
    });
    
    // AJAX handler for creating program from template
    add_action('wp_ajax_skybug_create_program_from_template', function() {
        check_ajax_referer('skybug_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $template = sanitize_key($_POST['template']);
        $program_name = sanitize_text_field($_POST['program_name']);
        
        if (!$template || !$program_name) {
            wp_send_json_error('Missing template or name');
        }
        
        $templates = array(
            'web' => array(
                'content' => "Dette er en web applikasjon.\n\nStandard funksjoner:\n- Brukerautentisering\n- Responsive design\n- API integration",
                'type' => 'web',
                'webhook_enabled' => true,
                'default_meta' => array(
                    '_skybug_api_enabled' => '1',
                    '_skybug_environment' => 'development'
                )
            ),
            'mobile' => array(
                'content' => "Dette er en mobil applikasjon.\n\nPlattformer:\n- iOS\n- Android\n\nFunksjoner:\n- Push notifications\n- Offline support",
                'type' => 'mobile',
                'webhook_enabled' => true,
                'default_meta' => array(
                    '_skybug_api_enabled' => '1',
                    '_skybug_platform' => 'cross-platform'
                )
            ),
            'api' => array(
                'content' => "Dette er en REST API service.\n\nEndpoints:\n- GET /api/v1/\n- POST /api/v1/\n\nAuthentication: Bearer token",
                'type' => 'api',
                'webhook_enabled' => false,
                'default_meta' => array(
                    '_skybug_api_enabled' => '1',
                    '_skybug_version' => 'v1.0'
                )
            ),
            'service' => array(
                'content' => "Dette er en microservice.\n\nAnsvar:\n- Data processing\n- Background jobs\n- Integration",
                'type' => 'service',
                'webhook_enabled' => false,
                'default_meta' => array(
                    '_skybug_api_enabled' => '0',
                    '_skybug_service_type' => 'background'
                )
            )
        );
        
        if (!isset($templates[$template])) {
            wp_send_json_error('Invalid template');
        }
        
        $template_data = $templates[$template];
        
        // Create program post
        $program_id = wp_insert_post(array(
            'post_title' => $program_name,
            'post_content' => $template_data['content'],
            'post_type' => 'skybug_program',
            'post_status' => 'publish'
        ));
        
        if (is_wp_error($program_id)) {
            wp_send_json_error($program_id->get_error_message());
        }
        
        // Set program type
        wp_set_post_terms($program_id, array($template_data['type']), 'skybug_program_type');
        
        // Apply default meta
        foreach ($template_data['default_meta'] as $key => $value) {
            update_post_meta($program_id, $key, $value);
        }
        
        // Generate API key
        $api_key = 'sky_' . wp_generate_password(32, false);
        update_post_meta($program_id, '_skybug_api_key', $api_key);
        
        // Set webhook if enabled
        if ($template_data['webhook_enabled']) {
            update_post_meta($program_id, '_skybug_webhook_url', home_url('/wp-json/skybug/v1/webhook/' . $program_id));
        }
        
        wp_send_json_success(array(
            'program_id' => $program_id,
            'edit_url' => get_edit_post_link($program_id),
            'message' => 'Program opprettet fra mal'
        ));
    });
    
    // AJAX handler for checking repository commits
    add_action('wp_ajax_skybug_check_repo_commits', function() {
        check_ajax_referer('skybug_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $program_id = intval($_POST['program_id']);
        if (!$program_id) {
            wp_send_json_error('Invalid program ID');
        }
        
        $repo_url = get_post_meta($program_id, '_skybug_repo_url', true);
        if (!$repo_url) {
            wp_send_json_error('No repository URL configured');
        }
        
        // Parse GitHub/GitLab URL
        $commits = array();
        if (strpos($repo_url, 'github.com') !== false) {
            $commits = skybug_fetch_github_commits($repo_url);
        } elseif (strpos($repo_url, 'gitlab.com') !== false) {
            $commits = skybug_fetch_gitlab_commits($repo_url);
        } else {
            wp_send_json_error('Unsupported repository type');
        }
        
        if (is_wp_error($commits)) {
            wp_send_json_error($commits->get_error_message());
        }
        
        wp_send_json_success($commits);
    });
    
    // AJAX handler for API endpoint testing
    add_action('wp_ajax_skybug_test_api_endpoint', function() {
        check_ajax_referer('skybug_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $program_id = intval($_POST['program_id']);
        $method = sanitize_text_field($_POST['method']);
        $endpoint = sanitize_text_field($_POST['endpoint']);
        
        if (!$program_id || !$method || !$endpoint) {
            wp_send_json_error('Missing required parameters');
        }
        
        // Get program API key
        $api_key = get_post_meta($program_id, '_skybug_api_key', true);
        if (!$api_key) {
            wp_send_json_error('No API key configured');
        }
        
        // Get base URL (try to guess from webhook or use site URL)
        $webhook_url = get_post_meta($program_id, '_skybug_webhook_url', true);
        $base_url = home_url();
        
        if ($webhook_url) {
            $parsed = parse_url($webhook_url);
            $base_url = $parsed['scheme'] . '://' . $parsed['host'];
        }
        
        $test_url = rtrim($base_url, '/') . '/' . ltrim($endpoint, '/');
        
        // Prepare request
        $args = array(
            'method' => $method,
            'timeout' => 10,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
                'User-Agent' => 'SkyBug-API-Tester'
            )
        );
        
        // Add test data for POST/PUT
        if (in_array($method, ['POST', 'PUT'])) {
            $args['body'] = json_encode(array('test' => true, 'timestamp' => time()));
        }
        
        $start_time = microtime(true);
        $response = wp_remote_request($test_url, $args);
        $end_time = microtime(true);
        
        $response_time = round(($end_time - $start_time) * 1000, 2); // ms
        
        if (is_wp_error($response)) {
            wp_send_json_success(array(
                'success' => false,
                'error' => $response->get_error_message(),
                'response_time' => $response_time,
                'url' => $test_url
            ));
            return;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $headers = wp_remote_retrieve_headers($response);
        
        // Try to format JSON response
        $formatted_body = $body;
        $json_data = json_decode($body, true);
        if ($json_data) {
            $formatted_body = json_encode($json_data, JSON_PRETTY_PRINT);
        }
        
        wp_send_json_success(array(
            'success' => true,
            'status_code' => $status_code,
            'response_time' => $response_time,
            'body' => $formatted_body,
            'headers' => $headers->getAll(),
            'url' => $test_url
        ));
    });
    
    if(class_exists('WP_CLI')) {
        WP_CLI::add_command('skybug metrics', [new SkyBug_CLI_Command(), 'metrics']);
        WP_CLI::add_command('skybug flush-metrics', [new SkyBug_CLI_Command(), 'flush_metrics']);
    }
}

// Repository integration functions
function skybug_fetch_github_commits($repo_url) {
    // Extract owner/repo from URL
    if (!preg_match('#github\.com/([^/]+)/([^/]+)#', $repo_url, $matches)) {
        return new WP_Error('invalid_url', 'Invalid GitHub URL format');
    }
    
    $owner = $matches[1];
    $repo = rtrim($matches[2], '.git');
    
    $api_url = "https://api.github.com/repos/{$owner}/{$repo}/commits?per_page=5";
    
    $response = wp_remote_get($api_url, array(
        'timeout' => 10,
        'headers' => array(
            'User-Agent' => 'SkyBug-Plugin'
        )
    ));
    
    if (is_wp_error($response)) {
        return $response;
    }
    
    $body = wp_remote_retrieve_body($response);
    $commits = json_decode($body, true);
    
    if (!$commits) {
        return new WP_Error('api_error', 'Failed to fetch commits');
    }
    
    $formatted_commits = array();
    foreach ($commits as $commit) {
        $formatted_commits[] = array(
            'sha' => substr($commit['sha'], 0, 7),
            'message' => $commit['commit']['message'],
            'author' => $commit['commit']['author']['name'],
            'date' => date('d.m.Y H:i', strtotime($commit['commit']['author']['date'])),
            'url' => $commit['html_url']
        );
    }
    
    return $formatted_commits;
}

function skybug_fetch_gitlab_commits($repo_url) {
    // Extract project path from URL
    if (!preg_match('#gitlab\.com/(.+?)(?:\.git)?(?:/.*)?$#', $repo_url, $matches)) {
        return new WP_Error('invalid_url', 'Invalid GitLab URL format');
    }
    
    $project_path = urlencode($matches[1]);
    $api_url = "https://gitlab.com/api/v4/projects/{$project_path}/repository/commits?per_page=5";
    
    $response = wp_remote_get($api_url, array(
        'timeout' => 10,
        'headers' => array(
            'User-Agent' => 'SkyBug-Plugin'
        )
    ));
    
    if (is_wp_error($response)) {
        return $response;
    }
    
    $body = wp_remote_retrieve_body($response);
    $commits = json_decode($body, true);
    
    if (!$commits) {
        return new WP_Error('api_error', 'Failed to fetch commits');
    }
    
    $formatted_commits = array();
    foreach ($commits as $commit) {
        $formatted_commits[] = array(
            'sha' => substr($commit['id'], 0, 7),
            'message' => $commit['message'],
            'author' => $commit['author_name'],
            'date' => date('d.m.Y H:i', strtotime($commit['authored_date'])),
            'url' => $commit['web_url']
        );
    }
    
    return $formatted_commits;
}