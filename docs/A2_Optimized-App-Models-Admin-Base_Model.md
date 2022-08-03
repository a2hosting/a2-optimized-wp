A2_Optimized\App\Models\Admin\Base_Model
===============

Blueprint for Admin related Models. All Admin Models should extend this Base_Model




* Class name: Base_Model
* Namespace: A2_Optimized\App\Models\Admin
* This is an **abstract** class
* Parent class: [A2_Optimized\Core\Model](A2_Optimized-Core-Model.md)







Methods
-------


### register_hook_callbacks

    mixed A2_Optimized\App\Models\Admin\Base_Model::register_hook_callbacks()

Register callbacks for actions and filters. Most of your add_action/add_filter
go into this method.

NOTE: register_hook_callbacks method is not called automatically. You
as a developer have to call this method where you see fit. For Example,
You may want to call this in constructor, if you feel hooks/filters
callbacks should be registered when the new instance of the class
is created.

The purpose of this method is to set the convention that first place to
find add_action/add_filter is register_hook_callbacks method.

This method is not marked abstract because it may not be needed in every
model. Making it abstract would enforce every child class to implement
the method.

If I were you, I would define register_hook_callbacks method in the child
class when it is a 'Model only' route. This is not a rule, it
is just my opinion when I would define this method.

* Visibility: **protected**




### get_instance

    object A2_Optimized\Core\Model::get_instance()

Provides access to a single instance of a module using the singleton pattern



* Visibility: **public**
* This method is **static**.
* This method is defined by [A2_Optimized\Core\Model](A2_Optimized-Core-Model.md)



