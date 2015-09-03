<?php
    
namespace Quan\AbNetLib;

class Boilerplate
{
    protected $file;
    protected $path;

    public function __construct($file, $func = null)
    {
        // parent variable
        $this->file = $file;
        
        // check if acf plugin is active and pass custom (static) activation function on activation
        $this->activationHook($func);
        // check if acf plugin is active
        $this->checkDependencies();

        // setup acf to import this plugins fields
        $this->includeFields();
    }
    
    private function includeFields()
    {
        $this->path = \plugin_basename($this->file);
        add_filter('acf/settings/load_json', function($paths) {
            $paths[] = $this->getPath() . '/inc';
            
            return $paths;
        }, 9);
    }

    public function getPath()
    {
        $path = explode('/', $this->path);

        return trailingslashit(WP_PLUGIN_DIR) . $path[0];
    }

    private function checkDependencies()
    {
        add_action('admin_init', function() {
            if (!class_exists('\acf')) {
                deactivate_plugins($this->path);
                add_action('admin_notices', function() {
                    echo '<div class="error"><p>Please activate <a href="https://wordpress.org/plugins/advanced-custom-fields/" target="_blank">Advanced Custom Fields</a> first.</p></div>';
                });
            }
        });
    }

    private function activationHook($func)
    {
        \register_activation_hook($this->file, function() use ($func) {
            $this->checkDependencies();
            if (!is_null($func)) {
                call_user_func($func);
            }
        });
    }
}