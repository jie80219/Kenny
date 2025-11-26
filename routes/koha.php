<?php

use Slim\App;


// SIERRA
$app->group('/sierra', function () use ($app) {
    $app->group('/patron', function () use ($app) {
        $app->get('', \SierraController::class . ':get_patron');
        $app->post('', \SierraController::class . ':post_patron');
        $app->patch('', \SierraController::class . ':patch_patron');
        $app->delete('', \SierraController::class . ':delete_patron');
        $app->group('/validate', function () use ($app) {
            $app->get('', \SierraController::class . ':get_patron_validate');
        });
    });

    $app->group('/card_reader', function () use ($app) {
        $app->get('', \SierraController::class . ':check_card_reader_data');
    });
    $app->group('/checkout', function () use ($app) {
        $app->get('', \SierraController::class . ':get_checkout');
        //$app->post('', \SierraController::class . ':post_checkout');
        //$app->patch('', \SierraController::class . ':patch_checkout');
        //$app->delete('', \SierraController::class . ':delete_checkout');
        $app->group('/history', function () use ($app) {
            $app->get('', \SierraController::class . ':get_checkout_history');
        });
        $app->group('/overdue', function () use ($app) {
            $app->get('', \SierraController::class . ':get_checkout_overdue');
        });
        $app->group('/statistic', function () use ($app) {
            $app->group('/top100', function () use ($app) {
                $app->get('', \SierraController::class . ':get_checkout_statistic_top100');
            });
            $app->group('/pcode3', function () use ($app) {
                $app->get('', \SierraController::class . ':get_checkout_statistic_pcode3');
            });
            $app->group('/ptype', function () use ($app) {
                $app->get('', \SierraController::class . ':get_checkout_statistic_ptype');
            });
        });
    });
    $app->group('/hold', function () use ($app) {
        $app->get('', \SierraController::class . ':get_hold');
        $app->post('', \SierraController::class . ':post_hold');
        $app->patch('', \SierraController::class . ':patch_hold');
        $app->delete('', \SierraController::class . ':delete_hold');
        $app->group('/arrive', function () use ($app) {
            $app->get('', \SierraController::class . ':get_hold_arrive');
        });
    });
    $app->group('/fine', function () use ($app) {
        $app->get('', \SierraController::class . ':get_fine');
        $app->group('/outstanding', function () use ($app) {
            $app->get('', \SierraController::class . ':get_fine_outstanding');
        });
    });
    // $app->group('/bib', function () use ($app) {
    //     $app->get('', \SierraController::class . ':get_bib');
    // });
    // $app->group('/item', function () use ($app) {
    //     $app->get('', \SierraController::class . ':get_item');
    // });
    $app->group('/token', function () use ($app) {
        $app->get('', \SierraController::class . ':get_token');
    });
    $app->group('/mail', function () use ($app) {
        $app->get('', \SierraController::class . ':get_mail');
    });
});
// KOHA
$app->group('/koha', function () use ($app) {
    $app->group('/library', function () use ($app) {
        $app->get('', \KohaController::class . ':get_library');
        $app->post('', \KohaController::class . ':post_library');
        $app->patch('', \KohaController::class . ':patch_library');
        $app->delete('', \KohaController::class . ':delete_library');
    });
    $app->group('/borrower', function () use ($app) {
        $app->get('', \KohaController::class . ':get_borrower');
        $app->post('', \KohaController::class . ':post_borrower');
        $app->patch('', \KohaController::class . ':patch_borrower');
        $app->delete('', \KohaController::class . ':delete_borrower');
        $app->group('/validate', function () use ($app) {
            $app->get('', \KohaController::class . ':get_borrower_validate');
        });
        $app->group('/password', function () use ($app) {
            $app->post('', \KohaController::class . ':post_borrower_password');
        });
        $app->group('/category', function () use ($app) {
            $app->get('', \KohaController::class . ':get_borrower_category');
            $app->post('', \KohaController::class . ':post_borrower_category');
            $app->patch('', \KohaController::class . ':patch_borrower_category');
            $app->delete('', \KohaController::class . ':delete_borrower_category');
        });
        $app->group('/debarred', function () use ($app) {
            $app->get('', \KohaController::class . ':get_borrower_debarred');
            $app->post('', \KohaController::class . ':post_borrower_debarred');
            $app->delete('', \KohaController::class . ':delete_borrower_debarred');
        });
        $app->group('/permissions', function () use ($app) {
            $app->get('', \KohaController::class . ':get_borrower_permissions');
            $app->patch('', \KohaController::class . ':patch_borrower_permissions');
        });
        $app->group('/map2user', function () use ($app) {
            $app->get('', \KohaController::class . ':get_borrower_map2user');
            $app->post('', \KohaController::class . ':post_borrower_map2user');
            $app->patch('', \KohaController::class . ':patch_borrower_map2user');
            $app->delete('', \KohaController::class . ':delete_borrower_map2user');
        });
		$app->group('/attribute_types', function () use ($app) {
			$app->get('', \KohaController::class . ':get_borrower_attribute_types');
			$app->post('', \KohaController::class . ':post_borrower_attribute_types');
			$app->patch('', \KohaController::class . ':patch_borrower_attribute_types');
			$app->delete('', \KohaController::class . ':delete_borrower_attribute_types');
			$app->group('/type', function () use ($app) {
				$app->get('', \KohaController::class . ':get_borrower_attribute_types_type');
			});
		});
	});

    $app->group('/category', function () use ($app) {
        $app->group('/type', function () use ($app) {
            $app->get('', \KohaController::class . ':get_category_type');
        });
    });
    $app->group('/authorised_values', function () use ($app) {
        $app->group('/category', function () use ($app) {
            $app->get('', \KohaController::class . ':get_authorised_values_category');
            $app->post('', \KohaController::class . ':post_authorised_values_category');
            $app->delete('', \KohaController::class . ':delete_authorised_values_category');
        });
        $app->get('', \KohaController::class . ':get_authorised_values');
        $app->post('', \KohaController::class . ':post_authorised_values');
        $app->patch('', \KohaController::class . ':patch_authorised_values');
        $app->delete('', \KohaController::class . ':delete_authorised_values');
    });
	$app->group('/itemtypes', function () use ($app) {
		$app->get('', \KohaController::class . ':get_itemtypes');
		$app->post('', \KohaController::class . ':post_itemtypes');
		$app->patch('', \KohaController::class . ':patch_itemtypes');
		$app->delete('', \KohaController::class . ':delete_itemtypes');
	});

	$app->group('/card_reader', function () use ($app) {
        $app->get('', \KohaController::class . ':check_card_reader_data');
    });
    $app->group('/checkin', function () use ($app) {
        $app->post('', \KohaController::class . ':post_checkin');
    });
    $app->group('/checkout', function () use ($app) {
        $app->get('', \KohaController::class . ':get_checkout');
        $app->post('', \KohaController::class . ':post_checkout');
        //$app->patch('', \KohaController::class . ':patch_checkout');
        //$app->delete('', \KohaController::class . ':delete_checkout');
        $app->group('/renew', function () use ($app) {
            $app->post('', \KohaController::class . ':post_checkout_renew');
            $app->get('/history', \KohaController::class . ':get_checkout_renew_history');
            $app->delete('/history', \KohaController::class . ':delete_checkout_renew_history');
        });
        $app->group('/history', function () use ($app) {
            $app->get('', \KohaController::class . ':get_checkout_history');
            $app->delete('', \KohaController::class . ':delete_checkout_history');
        });
        $app->group('/overdue', function () use ($app) {
            $app->get('', \KohaController::class . ':get_checkout_overdue');
        });
        $app->group('/statistic', function () use ($app) {
            $app->group('/top100', function () use ($app) {
                $app->get('', \KohaController::class . ':get_checkout_statistic_top100');
            });
            $app->group('/pcode3', function () use ($app) {
                $app->get('', \KohaController::class . ':get_checkout_statistic_pcode3');
            });
            $app->group('/ptype', function () use ($app) {
                $app->get('', \KohaController::class . ':get_checkout_statistic_ptype');
            });
        });
    });
    $app->group('/hold', function () use ($app) {
        $app->get('', \KohaController::class . ':get_hold');
        $app->post('', \KohaController::class . ':post_hold');
        $app->patch('', \KohaController::class . ':patch_hold');
        $app->delete('', \KohaController::class . ':delete_hold');
        $app->patch('/priority', \KohaController::class . ':patch_hold_priority');
        $app->group('/arrive', function () use ($app) {
            $app->get('', \KohaController::class . ':get_hold_arrive');
        });
    });
    $app->group('/fine', function () use ($app) {
        $app->get('', \KohaController::class . ':get_fine');
        $app->group('/outstanding', function () use ($app) {
            $app->get('', \KohaController::class . ':get_fine_outstanding');
        });
    });
    $app->group('/bib', function () use ($app) {
        $app->get('', \KohaController::class . ':get_bib');
        $app->post('', \KohaController::class . ':post_bib');
        $app->patch('', \KohaController::class . ':patch_bib');
        $app->delete('', \KohaController::class . ':delete_bib');
        $app->group('/z3950', function () use ($app) {
            $app->get('', \KohaController::class . ':get_bib_z3950');
        });
        $app->group('/marcFile2Json', function () use ($app) {
            $app->post('', \KohaController::class . ':post_bib_marcFile2Json');
        });
    });
    $app->group('/item', function () use ($app) {
        $app->get('', \KohaController::class . ':get_item');
        $app->group('/api', function () use ($app) {
            $app->get('', \KohaController::class . ':get_item_api');
        });
        $app->post('', \KohaController::class . ':post_item');
        $app->patch('', \KohaController::class . ':patch_item');
        $app->delete('', \KohaController::class . ':delete_item');
        $app->group('/label', function () use ($app) {
            $app->post('', \KohaController::class . ':post_item_label');
        });
        $app->group('_import', function () use ($app) {
            $app->get('/manual',  \KohaController::class . ':get_item_import_manual');
            $app->post('/read_sample',  \KohaController::class . ':get_item_import_data');
        });
        $app->group('_export', function () use ($app) {
            $app->get('/excel',  \KohaController::class . ':get_item_export_excel');
        });
    });
    $app->group('/marc', function () use ($app) {
        $app->group('/tagStructure', function () use ($app) {
            $app->get('', \KohaController::class . ':get_marc_tagStructure');
        });
    });
    $app->group('/message', function () use ($app) {
        $app->group('/type', function () use ($app) {
            $app->get('', \KohaController::class . ':get_message_type');
        });
    });
    $app->group('/authority', function () use ($app) {
        $app->get('', \KohaController::class . ':get_authority');
        $app->post('', \KohaController::class . ':post_authority');
        $app->patch('', \KohaController::class . ':patch_authority');
        $app->delete('', \KohaController::class . ':delete_authority');
        $app->group('/type', function () use ($app) {
            $app->get('', \KohaController::class . ':get_authority_type');
        });
    });
    $app->group('/circulation_rules', function () use ($app) {
        $app->get('', \KohaController::class . ':get_circulation_rules');
        $app->post('', \KohaController::class . ':post_circulation_rules');
        $app->patch('', \KohaController::class . ':patch_circulation_rules');
        $app->delete('', \KohaController::class . ':delete_circulation_rules');
        $app->group('/type', function () use ($app) {
            $app->get('', \KohaController::class . ':get_circulation_rules_type');
        });
    });
    $app->group('/vendor', function () use ($app) {
        $app->get('', \KohaController::class . ':get_vendor');
        $app->post('', \KohaController::class . ':post_vendor');
        $app->patch('', \KohaController::class . ':patch_vendor');
        $app->delete('', \KohaController::class . ':delete_vendor');
        $app->group('/type', function () use ($app) {
            $app->get('', \KohaController::class . ':get_vendor_type');
        });
    });
    $app->group('/serial', function () use ($app) {
        $app->get('', \KohaController::class . ':get_serial');
        $app->post('', \KohaController::class . ':post_serial');
        $app->patch('', \KohaController::class . ':patch_serial');
        $app->delete('', \KohaController::class . ':delete_serial');
        $app->group('/type', function () use ($app) {
            $app->get('', \KohaController::class . ':get_serial_type');
        });
        $app->group('/periodicity', function () use ($app) {
            $app->get('', \KohaController::class . ':get_serial_periodicity');
            $app->post('', \KohaController::class . ':post_serial_periodicity');
            $app->patch('', \KohaController::class . ':patch_serial_periodicity');
            $app->delete('', \KohaController::class . ':delete_serial_periodicity');
            $app->group('/type', function () use ($app) {
                $app->get('', \KohaController::class . ':get_serial_periodicity_type');
            });
        });
        $app->group('/numberingpattern', function () use ($app) {
            $app->get('', \KohaController::class . ':get_serial_numberingpattern');
            $app->post('', \KohaController::class . ':post_serial_numberingpattern');
            $app->patch('', \KohaController::class . ':patch_serial_numberingpattern');
            $app->delete('', \KohaController::class . ':delete_serial_numberingpattern');
            $app->group('/type', function () use ($app) {
                $app->get('', \KohaController::class . ':get_serial_numberingpattern_type');
            });
        });
    });
    $app->group('/cover_images', function () use ($app) {
        $app->group('/map2file', function () use ($app) {
            $app->get('', \KohaController::class . ':get_cover_images_map2file');
            $app->post('', \KohaController::class . ':post_cover_images_map2file');
            $app->patch('', \KohaController::class . ':patch_cover_images_map2file');
            $app->delete('', \KohaController::class . ':delete_cover_images_map2file');
        });
    });
    $app->group('/apiDemo', function () use ($app) {
	    $app->get('', \KohaController::class . ':renderTest');
	});
    $app->patch('/refresh_biblio_search_ts', \KohaController::class . ':patch_refresh_biblio_search_ts');
});
