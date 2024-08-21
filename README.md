# kazi-crud-new
# Kazi-Crud

    /** Documentation for laravel 11.X 
    *
    * --disable="migration,model,create_request,update_request,list_resource,detail_resource,controller,route" ----- optional
    * --fields="name:string,email:string,password:string" ----- optional
    * --methods="index,getAll,store,update,delete,show,changeStatus,getMetaData,export" ---- optional 
    * always use {model} name is small case in singular word ---- required 
    * soft delete, extra, created_by,updated_by are set in model and migration by default 
    * 
    * to use modules path modules package should be installed 
    * {module} name of module 
    * 
    * before using medias in filed make sure you have installed plank/Mediable package 
    * make sure your all logic for media has been set, 
    * --fields="medias:multiple" for multiple images 
    * --fields="medias:single" for single image 
    * **/

    command for generate crud
    -> php artisan generate:crud {model} {--module=} {--disable=} {--fields=} {--methods=}

    to use module feature please install Module Package from laravel-modules
    -> https://nwidart.com/laravel-modules/v6/installation-and-setup

    to use media feature please install Media form Plank Meduable
    -> https://laravel-mediable.readthedocs.io/en/latest/installation.html 
