<?php if ('joomla' === ACYM_CMS) { ?>
	<div class="acym__content acym_area padding-vertical-1 padding-horizontal-2 margin-bottom-2">
		<div class="acym__title acym__title__secondary"><?php echo acym_translation('ACYM_FRONTEND_EDITION'); ?></div>
        <?php
        if (acym_level(ACYM_ENTERPRISE)) {
            ?>
			<div class="grid-x grid-margin-x margin-y">
				<div class="cell medium-3"><?php echo acym_translation('ACYM_FRONT_DELETE_BUTTON').acym_info('ACYM_FRONT_DELETE_BUTTON_DESC'); ?></div>
				<div class="cell medium-9">
                    <?php
                    echo acym_radio(
                        [
                            'delete' => acym_translation('ACYM_DELETE_THE_SUBSCRIBER'),
                            'removesub' => acym_translation('ACYM_REMOVE_USER_SUBSCRIPTION'),
                        ],
                        'config[frontend_delete_button]',
                        $this->config->get('frontend_delete_button', 'delete')
                    );
                    ?>
				</div>
			</div>
            <?php
        }
        if (!acym_level(ACYM_ENTERPRISE)) {
            $data['version'] = 'enterprise';
            include acym_getView('dashboard', 'upgrade', true);
        }
        ?>
	</div>
<?php } ?>


<div class="acym__content acym_area padding-vertical-1 padding-horizontal-2 margin-bottom-2">
	<div class="acym__title acym__title__secondary"><?php echo acym_translation('ACYM_UNSUBSCRIBE_PAGE'); ?></div>
	<div class="grid-x grid-margin-x margin-y">
		<div class="cell grid-x margin-top-1 acym_vcenter">
            <?php
            echo acym_switch(
                'config[unsubscribe_page]',
                $this->config->get('unsubscribe_page', 1),
                acym_translation('ACYM_REDIRECT_ON_UNSUBSCRIBE_PAGE'),
                [],
                'xlarge-3 medium-5 small-9',
                'auto',
                '',
                'unsubpage_header'
            );
            ?>
		</div>
		<div class="cell grid-x margin-top-1" id="unsubpage_header">
			<div class="cell grid-x">
                <?php
                echo acym_switch(
                    'config[unsubpage_header]',
                    $this->config->get('unsubpage_header', 0),
                    acym_translation('ACYM_UNSUBSCRIBE_PAGE_HEADER'),
                    [],
                    'xlarge-3 medium-5 small-9'
                );
                ?>
			</div>
		</div>
	</div>
</div>
