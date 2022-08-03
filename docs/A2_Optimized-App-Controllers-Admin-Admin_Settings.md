A2_Optimized\App\Controllers\Admin\Admin_Settings
===============

Controller class that implements Plugin Admin Settings configurations




* Class name: Admin_Settings
* Namespace: A2_Optimized\App\Controllers\Admin
* Parent class: [A2_Optimized\App\Controllers\Admin\Base_Controller](A2_Optimized-App-Controllers-Admin-Base_Controller.md)



Constants
----------


### SETTINGS_PAGE_SLUG

    const SETTINGS_PAGE_SLUG = \A2_Optimized::PLUGIN_ID





### REQUIRED_CAPABILITY

    const REQUIRED_CAPABILITY = 'manage_options'





Properties
----------


### $hook_suffix

    private string $hook_suffix = 'settings_page_' . \A2_Optimized::PLUGIN_ID

Holds suffix for dynamic add_action called on settings page.



* Visibility: **private**
* This property is **static**.


### $model

    protected Object $model

Holds Model object



* Visibility: **protected**


### $view

    protected Object $view

Holds View Object



* Visibility: **protected**


Methods
-------


### register_hook_callbacks

    mixed A2_Optimized\App\Controllers\Admin\Base_Controller::register_hook_callbacks()

Register callbacks for actions and filters. Most of your add_action/add_filter
go into this method.

NOTE: register_hook_callbacks method is not called automatically. You
as a developer have to call this method where you see fit. For Example,
You may want to call this in constructor, if you feel hooks/filters
callbacks should be registered when the new instance of the class
is created.

The purpose of this method is to set the convention that first place to
find add_action/add_filter is register_hook_callbacks method.

* Visibility: **protected**
* This method is **abstract**.
* This method is defined by [A2_Optimized\App\Controllers\Admin\Base_Controller](A2_Optimized-App-Controllers-Admin-Base_Controller.md)




### plugin_menu

    mixed A2_Optimized\App\Controllers\Admin\Admin_Settings::plugin_menu()

Create menu for Plugin inside Settings menu



* Visibility: **public**




### enqueue_scripts

    mixed A2_Optimized\App\Controllers\Admin\Admin_Settings::enqueue_scripts()

Register the JavaScript for the admin area.



* Visibility: **public**




### enqueue_styles

    mixed A2_Optimized\App\Controllers\Admin\Admin_Settings::enqueue_styles()

Register the JavaScript for the admin area.



* Visibility: **public**




### markup_settings_page

    mixed A2_Optimized\App\Controllers\Admin\Admin_Settings::markup_settings_page()

Creates the markup for the Settings page



* Visibility: **public**




### register_fields

    mixed A2_Optimized\App\Controllers\Admin\Admin_Settings::register_fields()

Registers settings sections and fields



* Visibility: **public**




### markup_section_headers

    mixed A2_Optimized\App\Controllers\Admin\Admin_Settings::markup_section_headers(array $section)

Adds the section introduction text to the Settings page



* Visibility: **public**


#### Arguments
* $section **array** - &lt;p&gt;Array containing information Section Id, Section
                      Title &amp; Section Callback.&lt;/p&gt;



### markup_fields

    mixed A2_Optimized\App\Controllers\Admin\Admin_Settings::markup_fields(array $field_args)

Delivers the markup for settings fields



* Visibility: **public**


#### Arguments
* $field_args **array** - &lt;p&gt;Field arguments passed in &lt;code&gt;add_settings_field&lt;/code&gt;
function.&lt;/p&gt;



### add_plugin_action_links

    array A2_Optimized\App\Controllers\Admin\Admin_Settings::add_plugin_action_links(array $links)

Adds links to the plugin's action link section on the Plugins page



* Visibility: **public**


#### Arguments
* $links **array** - &lt;p&gt;The links currently mapped to the plugin.&lt;/p&gt;



### get_instance

    object A2_Optimized\Core\Controller::get_instance(mixed $model_class_name, mixed $view_class_name)

Provides access to a single instance of a module using the singleton pattern



* Visibility: **public**
* This method is **static**.
* This method is defined by [A2_Optimized\Core\Controller](A2_Optimized-Core-Controller.md)


#### Arguments
* $model_class_name **mixed** - &lt;p&gt;Model Class to be associated with the controller.&lt;/p&gt;
* $view_class_name **mixed** - &lt;p&gt;View Class to be associated with the controller.&lt;/p&gt;



### get_model

    object A2_Optimized\Core\Controller::get_model()

Get model.

In most of the cases, the model will be set as per routes in defined in routes.php.
So if you are not sure which model class is currently being used, search for the
current controller class name in the routes.php

* Visibility: **protected**
* This method is defined by [A2_Optimized\Core\Controller](A2_Optimized-Core-Controller.md)




### get_view

    object A2_Optimized\Core\Controller::get_view()

Get view

In most of the cases, the view will be set as per routes in defined in routes.php.
So if you are not sure which view class is currently being used, search for the
current controller class name in the routes.php

* Visibility: **protected**
* This method is defined by [A2_Optimized\Core\Controller](A2_Optimized-Core-Controller.md)




### set_model

    void A2_Optimized\Core\Controller::set_model(\A2_Optimized\Core\Model $model)

Sets the model to be used



* Visibility: **protected**
* This method is defined by [A2_Optimized\Core\Controller](A2_Optimized-Core-Controller.md)


#### Arguments
* $model **[A2_Optimized\Core\Model](A2_Optimized-Core-Model.md)** - &lt;p&gt;Model object to be associated with the current controller object.&lt;/p&gt;



### set_view

    void A2_Optimized\Core\Controller::set_view(\A2_Optimized\Core\View $view)

Sets the view to be used



* Visibility: **protected**
* This method is defined by [A2_Optimized\Core\Controller](A2_Optimized-Core-Controller.md)


#### Arguments
* $view **[A2_Optimized\Core\View](A2_Optimized-Core-View.md)** - &lt;p&gt;View object to be associated with the current controller object.&lt;/p&gt;



### __construct

    mixed A2_Optimized\Core\Controller::__construct(\A2_Optimized\Core\Model $model, mixed $view)

Constructor



* Visibility: **protected**
* This method is defined by [A2_Optimized\Core\Controller](A2_Optimized-Core-Controller.md)


#### Arguments
* $model **[A2_Optimized\Core\Model](A2_Optimized-Core-Model.md)** - &lt;p&gt;Model object to be used with current controller object.&lt;/p&gt;
* $view **mixed** - &lt;p&gt;View object to be used with current controller object. Otherwise false.&lt;/p&gt;



### init

    void A2_Optimized\Core\Controller::init(\A2_Optimized\Core\Model $model, mixed $view)

Sets Model & View to be used with current controller



* Visibility: **protected**
* This method is defined by [A2_Optimized\Core\Controller](A2_Optimized-Core-Controller.md)


#### Arguments
* $model **[A2_Optimized\Core\Model](A2_Optimized-Core-Model.md)** - &lt;p&gt;Model to be associated with this controller.&lt;/p&gt;
* $view **mixed** - &lt;p&gt;Either View/its child class object or False.&lt;/p&gt;


