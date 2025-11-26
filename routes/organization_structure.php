<?php

use Slim\App;

//內層 - 需要登入
$app->group('', function () use ($app) {
    $app->group('/organization_structure', function () use ($app) {
        $app->group('/api', function () use ($app) {
            $app->group('/role', function () use ($app) {
                $app->group('_depth', function () use ($app) {
                    $app->get('', \organization_structurecontroller::class . ':get_role_depth');
                    // $app->post('', \organization_structurecontroller::class . ':post_role_depth');
                    $app->patch('', \organization_structurecontroller::class . ':patch_role_depth');
                    // $app->delete('', \organization_structurecontroller::class . ':delete_role_depth');
                });
                $app->group('_property_json', function () use ($app) {
                    $app->get('', \organization_structurecontroller::class . ':get_role_property_json');
                    $app->post('', \organization_structurecontroller::class . ':post_role_property_json');
                    $app->patch('', \organization_structurecontroller::class . ':patch_role_property_json');
                    // $app->delete('', \organization_structurecontroller::class . ':delete_role_depth');
                });
                $app->get('', \organization_structurecontroller::class . ':get_role');
                $app->post('', \organization_structurecontroller::class . ':post_role');
                $app->patch('', \organization_structurecontroller::class . ':patch_role');
                $app->delete('', \organization_structurecontroller::class . ':delete_role');
            });
            $app->group('/department', function () use ($app) {
                $app->group('_depth', function () use ($app) {
                    $app->get('', \organization_structurecontroller::class . ':get_department_depth');
                    // $app->post('', \organization_structurecontroller::class . ':post_department_depth');
                    $app->patch('', \organization_structurecontroller::class . ':patch_department_depth');
                    // $app->delete('', \organization_structurecontroller::class . ':delete_department_depth');
                });
                $app->group('_permission', function () use ($app) {
                    $app->get('', \organization_structurecontroller::class . ':get_department_permission');
                    $app->post('', \organization_structurecontroller::class . ':post_department_permission');
                    $app->patch('', \organization_structurecontroller::class . ':patch_department_permission');
                    $app->delete('', \organization_structurecontroller::class . ':delete_department_permission');
                });
                $app->get('', \organization_structurecontroller::class . ':get_department');
                $app->post('', \organization_structurecontroller::class . ':post_department');
                $app->patch('', \organization_structurecontroller::class . ':patch_department');
                $app->delete('', \organization_structurecontroller::class . ':delete_department');
            });
            $app->group('/customer', function () use ($app) {
                $app->group('/role', function () use ($app) {
                    $app->get('', \organization_structurecontroller::class . ':get_customer_role');
                    $app->post('', \organization_structurecontroller::class . ':post_customer_role');
                    $app->patch('', \organization_structurecontroller::class . ':patch_customer_role');
                    $app->delete('', \organization_structurecontroller::class . ':delete_customer_role');
                });
                $app->group('/department', function () use ($app) {
                    $app->get('', \organization_structurecontroller::class . ':get_customer_department');
                    $app->post('', \organization_structurecontroller::class . ':post_customer_department');
                    $app->patch('', \organization_structurecontroller::class . ':patch_customer_department');
                    $app->delete('', \organization_structurecontroller::class . ':delete_customer_department');
                });
            });
            $app->group('/staff', function () use ($app) {
                $app->group('_self', function () use ($app) {
                    $app->get('', \organization_structurecontroller::class . ':get_staff_self');
                });
                $app->group('_diverge', function () use ($app) {
                    $app->group('', function () use ($app) {
                        $app->get('', \organization_structurecontroller::class . ':get_staff_diverge');
                        $app->patch('', \organization_structurecontroller::class . ':patch_staff_diverge');
                    });
                    $app->group('_self', function () use ($app) {
                        $app->get('', \organization_structurecontroller::class . ':get_staff_diverge_self');
                    });
                });
                $app->get('', \organization_structurecontroller::class . ':get_staff');
                $app->post('', \organization_structurecontroller::class . ':post_staff');
                $app->patch('', \organization_structurecontroller::class . ':patch_staff');
                $app->delete('', \organization_structurecontroller::class . ':delete_staff');
                $app->group('_import', function () use ($app) {
                    $app->get('/manual',  \organization_structurecontroller::class . ':get_staff_import_manual');
                    $app->post('/read_sample',  \organization_structurecontroller::class . ':get_staff_import_data');
                });
                $app->group('_export', function () use ($app) {
                    $app->get('/excel',  \organization_structurecontroller::class . ':get_staff_export_excel');
                });

                $app->group('/statistics', function () use ($app) {
                    $app->get('_gender', \organization_structurecontroller::class . ':get_statistics_staff_gender');
                    $app->get('_department', \organization_structurecontroller::class . ':get_statistics_staff_department');
                    $app->get('_role', \organization_structurecontroller::class . ':get_statistics_staff_role');
                });
            });
            $app->group('/gender', function () use ($app) {
                $app->get('', \organization_structurecontroller::class . ':get_gender');
                $app->post('', \organization_structurecontroller::class . ':post_gender');
                $app->patch('', \organization_structurecontroller::class . ':patch_gender');
                $app->delete('', \organization_structurecontroller::class . ':delete_gender');
            });
            $app->group('/user', function () use ($app) {
                $app->group('_role', function () use ($app) {
                    $app->get('', \organization_structurecontroller::class . ':user_role_select');
                    $app->post('', \organization_structurecontroller::class . ':user_role_insert');
                    $app->delete('', \organization_structurecontroller::class . ':user_role_delete');
                });
                $app->group('_parent', function () use ($app) {
                    $app->get('', \organization_structurecontroller::class . ':get_user_parent');
                });
                $app->group('', function () use ($app) {
                    $app->get('', \organization_structurecontroller::class . ':get_user');
                });
                $app->group('/reset_password', function () use ($app) {
                    $app->patch('',  \organization_structurecontroller::class . ':reset_user_password');
                });

                $app->group('/statistics', function () use ($app) {
                    $app->get('_line', \organization_structurecontroller::class . ':get_statistics_line');
                });
            });
        });
    });
    $app->group('/permission_management', function () use ($app) {
        $app->group('/permission', function () use ($app) {
            $app->group('/list', function () use ($app) {
                $app->get('', \permission_managementcontroller::class . ':get_permission_list');
            });
            $app->group('_group', function () use ($app) {
                $app->get('', \permission_managementcontroller::class . ':get_permission_group');
                $app->post('', \permission_managementcontroller::class . ':post_permission_group');
                $app->patch('', \permission_managementcontroller::class . ':patch_permission_group');
                $app->delete('', \permission_managementcontroller::class . ':delete_permission_group');
            });
            $app->group('_level', function () use ($app) {
                $app->get('', \permission_managementcontroller::class . ':get_permission_level');
                $app->post('', \permission_managementcontroller::class . ':post_permission_level');
                $app->patch('', \permission_managementcontroller::class . ':patch_permission_level');
                $app->delete('', \permission_managementcontroller::class . ':delete_permission_level');
            });
            $app->group('', function () use ($app) {
                $app->group('_manage', function () use ($app) {
                    $app->group('_self', function () use ($app) {
                        $app->get('', \permission_managementcontroller::class . ':get_permission_manage_self');
                        $app->post('', \permission_managementcontroller::class . ':post_permission_manage_self');
                        // $app->patch('', \permission_managementcontroller::class . ':patch_permission_manage_self');
                        $app->delete('', \permission_managementcontroller::class . ':delete_permission_manage_self');
                    });
                    $app->get('', \permission_managementcontroller::class . ':get_permission_manage');
                    $app->post('', \permission_managementcontroller::class . ':post_permission_manage');
                    // $app->patch('', \permission_managementcontroller::class . ':patch_permission_manage');
                    $app->delete('', \permission_managementcontroller::class . ':delete_permission_manage');
                });
                $app->get('', \permission_managementcontroller::class . ':get_permission');
                $app->post('', \permission_managementcontroller::class . ':post_permission');
                $app->patch('', \permission_managementcontroller::class . ':patch_permission');
                $app->delete('', \permission_managementcontroller::class . ':delete_permission');
            });
        });
    });
})->add('logincheck');
