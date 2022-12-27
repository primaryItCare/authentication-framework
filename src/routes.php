<?php 
use  AUTHWRAP\Userform\Controllers\OrganizationController;  

Route::group(['prefix' => '/organization'], function () {
    Route::get('/',[OrganizationController::class,'index'])->name('organization'); 
    Route::get('list',[OrganizationController::class,'getAjaxList'])->name('organization-list'); 
    Route::get('create',[OrganizationController::class,'create'])->name('organization-create'); 
    Route::get('edit/{id}',[OrganizationController::class,'edit'])->name('organization.edit'); 
    Route::get('view/{id}',[OrganizationController::class,'view'])->name('organization.view'); 
    Route::post('add-in-db',[OrganizationController::class,'organizationStore'])->name('organization.store'); 
    Route::delete('destroy',[OrganizationController::class,'destroy'])->name('organization.destroy'); 
}); 
?>