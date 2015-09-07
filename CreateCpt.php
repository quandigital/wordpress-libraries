<?php

namespace QuanDigital\WpLib;

class CreateCpt
{
    public function __construct($posttype, $singular, $plural, $icon)
    {
        $this->posttype = $posttype;
        $this->singular = $singular;
        $this->plural = $plural;
        $this->icon = $icon;

        $this->createCpt();
        $this->mapMetaCaps();
    }

    public function labels($singular, $plural) 
    {
        $labels = [
            'name' => \_x($plural, 'Post Type General Name', 'abnet' ),
            'singular_name' => \_x($singular, 'Post Type Singular Name', 'abnet' ),
            'menu_name' => \__($singular, 'abnet' ),
            'parent_item_colon' => \__('Parent ' . $singular . ':', 'abnet'),
            'all_items' => \__('All ' . $plural, 'abnet' ),
            'view_item' => \__('View ' . $singular, 'abnet' ),
            'add_new_item' => \__('Add New ' . $singular, 'abnet'),
            'add_new' => \__('New ' . $singular, 'abnet'),
            'edit_item' => \__('Edit ' . $singular, 'abnet'),
            'update_item' => \__('Update ' . $singular, 'abnet'),
            'search_items' => \__('Search ' . $plural, 'abnet'),
            'not_found' => \__('No ' . $plural . ' found', 'abnet'),
            'not_found_in_trash' => \__('No ' . $plural . ' found in Trash', 'abnet'),
        ];

        return $labels;
    }
    
    public function capabilities($posttype)
    {
        $capabilities = [
            'read_post' => 'read_' . $posttype,
            'read_private_posts' => 'read_private_' . $posttype,
            'publish_posts' => 'publish_' . $posttype . 's',
            'edit_posts' => 'edit_' . $posttype . 's',
            'edit_post' => 'edit_' . $posttype,
            'edit_others_posts' => 'edit_others_' . $posttype . 's',
            'edit_published_posts' => 'edit_published_' . $posttype . 's',
            'delete_posts' => 'delete_' . $posttype . 's',
            'delete_post' => 'delete_' . $posttype,
            'delete_published_posts' => 'delete_published_' . $posttype . 's',
            'delete_others_posts' => 'delete_others_' . $posttype . 's',
            'comment_' . $posttype . 's',
            'read_comments_' . $posttype . 's',
            'read_' . $posttype . 's',
        ];

        return $capabilities;
    }

    public function support()
    {
        $supports = [
            'supports' => ['title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions',],
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            'menu_position' => 5,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
        ];

        return $supports;
    }

    public function createCpt() 
    {
        $args = $this->support();

        $args['label'] = $this->posttype;
        $args['labels'] = $this->labels($this->singular, $this->plural);
        $args['capability_type'] = $this->posttype;
        $args['capabilities'] = $this->capabilities($this->posttype);
        $args['menu_icon'] = $this->icon;

        \add_action('init', function() use ($args) {
            \register_post_type($this->posttype, $args);
            $this->addCaps();
        });
    }

    public function addCaps()
    {
        $role = \get_role('administrator');
        foreach($this->capabilities($this->posttype) as $new_cap) {
            if (!empty($new_cap) && !$role->has_cap($new_cap)) {
                $role->add_cap($new_cap);
            }
        }
    }

    public function mapMetaCaps()
    {
        \add_filter('map_meta_cap', function($caps, $cap, $user_id, $args) {
            $cap_type = $this->posttype;

                /* If editing, deleting, or reading a cpt, get the post and post type object. */
                if ( 'edit_' . $cap_type == $cap || 'delete_' . $cap_type == $cap || 'read_' . $cap_type == $cap ) {
                    $post = \get_post($args[0]);
                    $post_type = \get_post_type_object( $post->post_type );

                    /* Set an empty array for the caps. */
                    $caps = array();
                }

                /* If editing a cpt, assign the required capability. */
                if ( 'edit_' . $cap_type == $cap ) {
                    if ( $user_id == $post->post_author )
                        $caps[] = $post_type->cap->edit_posts;
                    else
                        $caps[] = $post_type->cap->edit_others_posts;
                }

                /* If deleting a cpt, assign the required capability. */
                elseif ( 'delete_' . $cap_type == $cap ) {
                    if ( $user_id == $post->post_author )
                        $caps[] = $post_type->cap->delete_posts;
                    else
                        $caps[] = $post_type->cap->delete_others_posts;
                }

                /* If reading a private cpt, assign the required capability. */
                elseif ( 'read_' . $cap_type == $cap ) {

                    if ( 'private' != $post->post_status )
                        $caps[] = 'read';
                    elseif ( $user_id == $post->post_author )
                        $caps[] = 'read';
                    else
                        $caps[] = $post_type->cap->read_private_posts;
                }

            /* Return the capabilities required by the user. */
            return $caps;
        }, 10, 4);
    }

}