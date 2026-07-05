<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\TalukaController;
use App\Http\Controllers\VillageController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\NLPSVController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\PacsController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware(['auth:api', 'passport.check'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/getDistrict', [DistrictController::class, 'index']);
    Route::get('/districtList', [DistrictController::class, 'DistrictList']);
    Route::post('/saveDistrict', [DistrictController::class, 'save']);
    Route::put('/updateDistrict/{id}', [DistrictController::class, 'update']);
    Route::delete('/deleteDistrict/{id}', [DistrictController::class, 'delete']);

    Route::get('/getTaluka', [TalukaController::class, 'index']);
    Route::get('/talukaList', [TalukaController::class, 'TalukaList']);
    Route::post('/saveTaluka', [TalukaController::class, 'save']);
    Route::put('/updateTaluka/{id}', [TalukaController::class, 'update']);
    Route::delete('/deleteTaluka/{id}', [TalukaController::class, 'delete']);

    Route::get('/getVillage', [VillageController::class, 'index']);
    Route::get('/villageList', [VillageController::class, 'VillageList']);
    Route::post('/saveVillage', [VillageController::class, 'save']);
    Route::put('/updateVillage/{id}', [VillageController::class, 'update']);
    Route::delete('/deleteVillage/{id}', [VillageController::class, 'delete']);

    Route::get('/getBank', [BankController::class, 'index']);
    Route::get('/bankList', [BankController::class, 'bankList']);
    Route::post('/saveBank', [BankController::class, 'save']);
    Route::put('/updateBank/{id}', [BankController::class, 'update']);
    Route::delete('/deleteBank/{id}', [BankController::class, 'delete']);

    Route::get('/getBranch', [BranchController::class, 'index']);
    Route::get('/branchList', [BranchController::class, 'branchList']);
    Route::post('/saveBranch', [BranchController::class, 'save']);
    Route::put('/updateBranch/{id}', [BranchController::class, 'update']);
    Route::delete('/deleteBranch/{id}', [BranchController::class, 'delete']);

    Route::get('/getOrganization', [OrganizationController::class, 'index']);
    Route::get('/organizationList', [OrganizationController::class, 'organizationList']);
    Route::post('/saveOrganization', [OrganizationController::class, 'save']);
    Route::put('/updateOrganization/{id}', [OrganizationController::class, 'update']);
    Route::delete('/deleteOrganization/{id}', [OrganizationController::class, 'delete']);

    Route::get('/getDesignation', [DesignationController::class, 'index']);
    Route::get('/designationList', [DesignationController::class, 'designationList']);
    Route::post('/saveDesignation', [DesignationController::class, 'save']);
    Route::put('/updateDesignation/{id}', [DesignationController::class, 'update']);
    Route::delete('/deleteDesignation/{id}', [DesignationController::class, 'delete']);

    Route::get('/getPriority', [CommonController::class, 'getPriority']);
    Route::get('/getStatus', [CommonController::class, 'getStatus']);

    Route::get('/getProduct', [ProductController::class, 'index']);
    Route::get('/productList', [ProductController::class, 'productList']);
    Route::post('/saveProduct', [ProductController::class, 'save']);
    Route::put('/updateProduct/{id}', [ProductController::class, 'update']);
    Route::delete('/deleteProduct/{id}', [ProductController::class, 'delete']);
    Route::get('/getModuleByProductId/{id}', [ProductController::class, 'getModuleByProductId']);

    Route::get('/getModule', [ModuleController::class, 'index']);
    Route::get('/moduleList', [ModuleController::class, 'moduleList']);
    Route::post('/saveModule', [ModuleController::class, 'save']);
    Route::put('/updateModule/{id}', [ModuleController::class, 'update']);
    Route::delete('/deleteModule/{id}', [ModuleController::class, 'delete']);

    Route::get('/getTask', [TaskController::class, 'index']);
    Route::get('/taskList', [TaskController::class, 'taskList']);
    Route::post('/saveTask', [TaskController::class, 'save']);
    Route::put('/updateTask/{id}', [TaskController::class, 'update']);
    Route::delete('/deleteTask/{id}', [TaskController::class, 'delete']);

    Route::get('/getPacs', [PacsController::class, 'index']);
    Route::get('/getPacsById/{id}', [PacsController::class, 'getPacsById']);
    Route::get('/pacsList', [PacsController::class, 'pacsList']);
    Route::post('/savePacs', [PacsController::class, 'save']);
    Route::put('/updatePacs/{id}', [PacsController::class, 'update']);
    Route::delete('/deletePacs/{id}', [PacsController::class, 'delete']);
    Route::get('/getSocitySectionId', [PacsController::class, 'getSocitySectionId']);

    Route::get('/getOpenTickets', [TicketController::class, 'getOpenTickets']);
    Route::get('/getClosedTickets', [TicketController::class, 'getClosedTickets']);
    Route::get('/getInProgressTickets', [TicketController::class, 'getInProgressTickets']);
    Route::get('/getForwardedTickets', [TicketController::class, 'getForwardedTickets']);
    Route::get('/getForwardNlpsv', [TicketController::class, 'getForwardNlpsv']);
    Route::get('/getAllTickets', [TicketController::class, 'getAllTickets']);
    Route::get('/getTicketById/{id}', [TicketController::class, 'getTicketById']);
    Route::get('/getDashboardTicketCount', [TicketController::class, 'getDashboardTicketCount']);
    Route::get('/getRecentActivity', [TicketController::class, 'getRecentActivity']);
    Route::get('/getMonthlyTicketCount', [TicketController::class, 'getMonthlyTicketCount']);
    Route::post('/saveTicket', [TicketController::class, 'saveTicket']);
    Route::post('/ticketForwareded', [TicketController::class, 'forwardTicket']);
    Route::post('/getTicketReply', [TicketController::class, 'getTicketReply']);

    Route::get('/getRole', [RoleController::class, 'index']);
    Route::get('/roleList', [RoleController::class, 'roleList']);
    Route::post('/saveRole', [RoleController::class, 'save']);
    Route::put('/updateRole/{id}', [RoleController::class, 'update']);
    Route::delete('/deleteRole/{id}', [RoleController::class, 'delete']);

    Route::get('/getUser', [UserController::class, 'index']);
    Route::get('/userList', [UserController::class, 'userList']);
    Route::post('/saveUser', [UserController::class, 'save']);
    Route::put('/updateUser/{id}', [UserController::class, 'update']);
    Route::delete('/deleteUser/{id}', [UserController::class, 'delete']);
    Route::post('/changeUserPassword', [UserController::class, 'changeUserPassword']);
    Route::get('/getUserByRoleId/{role}', [UserController::class, 'getUserByRoleId']);

    Route::get('/getDistrictWiseReport', [ReportController::class, 'getDistrictWiseReport']);
    Route::get('/getProductwiseReport', [ReportController::class, 'getProductwiseReport']);

    Route::post('/NLPSVLoginApi', [NLPSVController::class, 'NLPSVLoginApi']);
    Route::post('/getNLPSVStatus', [NLPSVController::class, 'getNLPSVStatus']);
});
