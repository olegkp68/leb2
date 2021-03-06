<div class="acym__content acym_area padding-vertical-1 padding-horizontal-2 margin-bottom-2">
	<div class="acym__title acym__title__secondary"><?php echo acym_translation('ACYM_CONFIGURATION_QUEUE'); ?></div>
	<div class="grid-x grid-margin-x margin-y">
		<div class="cell medium-3"><?php echo acym_translation('ACYM_CONFIGURATION_QUEUE_PROCESSING'); ?></div>
		<div class="cell medium-9">
            <?php
            $queueModes = [
                'auto' => acym_translation('ACYM_CONFIGURATION_QUEUE_AUTOMATIC'),
                'automan' => acym_translation('ACYM_CONFIGURATION_QUEUE_AUTOMAN'),
                'manual' => acym_translation('ACYM_CONFIGURATION_QUEUE_MANUAL'),
            ];
            echo acym_radio(
                $queueModes,
                'config[queue_type]',
                $this->config->get('queue_type', 'automan'),
                [
                    'related' => [
                        'auto' => 'automatic_only',
                        'automan' => 'automatic_manual',
                        'manual' => 'manual_only',
                    ],
                ]
            );
            ?>
		</div>
		<div class="cell medium-3 automatic_only automatic_manual">
            <?php
            echo acym_translation('ACYM_AUTO_SEND_PROCESS');
            echo acym_info('ACYM_AUTO_SEND_PROCESS_DESC');
            ?>
		</div>
		<div class="cell medium-9 automatic_only automatic_manual">
            <?php
            $cronFrequency = $this->config->get('cron_frequency');
            $valueBatch = acym_level(ACYM_ENTERPRISE) ? $this->config->get('queue_batch_auto', 1) : 1;
            if (!function_exists('curl_multi_exec') && (intval($cronFrequency) < 900 || intval($valueBatch) > 1)) {
                acym_display(acym_translation('ACYM_MULTI_CURL_DISABLED'), 'error', false);
            } elseif (empty($cronFrequency) && !empty($this->config->get('active_cron', 0))) {
                acym_display(acym_translation('ACYM_EMPTY_FREQUENCY'), 'error', false);
            }

            $disabledBatch = acym_level(ACYM_ENTERPRISE) ? '' : 'disabled';
            $delayTypeAuto = $data['typeDelay'];
            $delayHtml = '<span data-acym-tooltip="'.acym_translation('ACYM_CRON_TRIGGERED_DESC').'">'.$delayTypeAuto->display(
                    'config[cron_frequency]',
                    $cronFrequency,
                    2
                ).'</span>';
            echo acym_translationSprintf(
                'ACYM_SEND_X_BATCH_OF_X_EMAILS_EVERY_Y',
                '<input '.$disabledBatch.' class="intext_input" type="text" name="config[queue_batch_auto]" value="'.$valueBatch.'" />',
                '<input class="intext_input" type="text" name="config[queue_nbmail_auto]" value="'.intval($this->config->get('queue_nbmail_auto')).'" />',
                $delayHtml
            ); ?>
		</div>
		<div class="cell medium-3 automatic_only automatic_manual"></div>
		<div class="cell medium-9 automatic_only automatic_manual">
            <?php
            $delayTypeAuto = $data['typeDelay'];
            echo acym_translationSprintf(
                'ACYM_WAIT_X_TIME_BETWEEN_MAILS',
                $delayTypeAuto->display('config[email_frequency]', $this->config->get('email_frequency', 0), 0)
            );
            ?>
		</div>
		<div class="cell medium-3 manual_only automatic_manual"><?php echo acym_translation('ACYM_MANUAL_SEND_PROCESS'); ?></div>
		<div class="cell medium-9 manual_only automatic_manual">
            <?php
            $delayTypeAuto = $data['typeDelay'];
            echo acym_translationSprintf(
                'ACYM_SEND_X_WAIT_Y',
                '<input class="intext_input" type="text" name="config[queue_nbmail]" value="'.intval($this->config->get('queue_nbmail')).'" />',
                $delayTypeAuto->display('config[queue_pause]', $this->config->get('queue_pause'), 0)
            );
            ?>
		</div>
		<div class="cell medium-3"><?php echo '<span>'.acym_translation('ACYM_MAX_NB_TRY').'</span>'.acym_info('ACYM_MAX_NB_TRY_DESC'); ?></div>
		<div class="cell medium-9">
            <?php echo acym_translationSprintf(
                'ACYM_CONFIG_TRY',
                '<input class="intext_input" type="text" name="config[queue_try]" value="'.intval($this->config->get('queue_try')).'">'
            );

            $failaction = $data['failaction'];
            echo ' '.acym_translationSprintf('ACYM_CONFIG_TRY_ACTION', $failaction->display('maxtry', $this->config->get('bounce_action_maxtry'))); ?>
		</div>
		<div class="cell medium-3"><?php echo acym_translation('ACYM_MAX_EXECUTION_TIME'); ?></div>
		<div class="cell medium-9">
            <?php
            echo acym_translationSprintf('ACYM_TIMEOUT_SERVER', ini_get('max_execution_time')).'<br />';
            $maxexecutiontime = intval($this->config->get('max_execution_time'));
            if (intval($this->config->get('last_maxexec_check')) > (time() - 20)) {
                echo acym_translationSprintf('ACYM_TIMEOUT_CURRENT', $maxexecutiontime);
            } else {
                if (!empty($maxexecutiontime)) {
                    echo acym_translationSprintf('ACYM_MAX_RUN', $maxexecutiontime).'<br />';
                }
                echo '<span id="timeoutcheck"><a id="timeoutcheck_action" class="acym__color__blue">'.acym_translation('ACYM_TIMEOUT_AGAIN').'</a></span>';
            }
            ?>
		</div>
		<div class="cell medium-3"><?php echo acym_translation('ACYM_ORDER_SEND_QUEUE'); ?></div>
		<div class="cell medium-9 grid-x">
			<div class="cell medium-6 large-4 xlarge-3 xxlarge-2">
                <?php
                echo acym_select(
                    [
                        acym_selectOption(
                            'user_id, ASC',
                            acym_translationSprintf('ACYM_COMBINED_TRANSLATIONS', acym_translation('ACYM_USER_ID'), acym_translation('ACYM_ASC'))
                        ),
                        acym_selectOption(
                            'user_id, DESC',
                            acym_translationSprintf('ACYM_COMBINED_TRANSLATIONS', acym_translation('ACYM_USER_ID'), acym_translation('ACYM_DESC'))
                        ),
                        acym_selectOption('rand', 'ACYM_RANDOM'),
                    ],
                    'config[sendorder]',
                    $this->config->get('sendorder', 'user_id, ASC'),
                    'class="acym__select"',
                    'value',
                    'text',
                    'sendorderid'
                );
                ?>
			</div>
		</div>
		<div class="cell medium-3"><?php echo acym_translation('ACYM_NUMBER_OF_DAYS_TO_CLEAN_QUEUE').acym_info('ACYM_NUMBER_OF_DAYS_TO_CLEAN_QUEUE_DESC'); ?></div>
		<div class="cell medium-9 grid-x">
			<div class="cell medium-6 large-4 xlarge-3 xxlarge-2">
				<input type="number" class="intext_input" min="0" name="config[queue_delete_days]" value="<?php echo $this->config->get('queue_delete_days', 0); ?>">
			</div>
		</div>
        <?php

        if (acym_level(ACYM_ESSENTIAL)) {
            $expirationDate = $this->config->get('expirationdate', 0);
            if (empty($expirationDate) || (time() - 604800) > $this->config->get('lastlicensecheck', 0)) {
                acym_checkVersion();
            }

            $cronUrl = acym_frontendLink('cron');

            if ($expirationDate > time()) {
                ?>
				<div class="cell medium-3 automatic_only automatic_manual"><?php echo acym_translation('ACYM_CRON_URL').acym_info('ACYM_CRON_URL_DESC'); ?></div>
				<div class="cell medium-9 automatic_only automatic_manual">
					<a class="acym__color__blue" href="<?php echo acym_escape($cronUrl); ?>" target="_blank"><?php echo $cronUrl; ?></a>
				</div>
                <?php
            }
        }
        ?>
	</div>
</div>
<?php
if (acym_level(ACYM_ESSENTIAL)) {
    ?>
	<div class="acym__content acym_area padding-vertical-1 padding-horizontal-2 margin-bottom-2">
		<div class="acym__title acym__title__secondary"><?php echo acym_translation('ACYM_REPORT'); ?></div>
		<div class="grid-x grid-margin-x margin-y">
			<div class="cell large-2 medium-3"><label for="cronsendreport"><?php echo acym_translation('ACYM_REPORT_SEND').acym_info('ACYM_REPORT_SEND_DESC'); ?></label></div>
			<div class="cell large-4 medium-9">
                <?php
                $cronreportval = [
                    '0' => 'ACYM_NO',
                    '1' => 'ACYM_EACH_TIME',
                    '2' => 'ACYM_ONLY_ACTION',
                    '3' => 'ACYM_ONLY_SOMETHING_WRONG',
                ];

                echo acym_select(
                    $cronreportval,
                    'config[cron_sendreport]',
                    $this->config->get('cron_sendreport', 0),
                    [
                        'class' => 'acym__select',
                        'acym-data-infinite' => '',
                    ],
                    'value',
                    'text',
                    'cronsendreport',
                    true
                );
                ?>
			</div>
			<div class="cell large-2 medium-3"><label for="cron_sendto"><?php echo acym_translation('ACYM_REPORT_SEND_TO').acym_info('ACYM_REPORT_SEND_TO_DESC'); ?></label></div>
			<div class="cell large-4 medium-9">
                <?php
                $emails = [];
                $receivers = $this->config->get('cron_sendto');
                if (!empty($receivers)) {
                    $receivers = explode(',', $receivers);
                    foreach ($receivers as $value) {
                        $emails[$value] = $value;
                    }
                }
                echo acym_selectMultiple(
                    $emails,
                    "config[cron_sendto]",
                    $emails,
                    ['id' => 'acym__configuration__cron__report--send-to', 'placeholder' => acym_translation('ACYM_MAILS')]
                ); ?>
			</div>
			<div class="cell large-2 medium-3"><label for="cronsavereport"><?php echo acym_translation('ACYM_REPORT_SAVE').acym_info('ACYM_REPORT_SAVE_DESC'); ?></label></div>
			<div class="cell large-4 medium-9">
                <?php
                $cronsave = [];
                $cronsave['0'] = acym_translation('ACYM_NO');
                $cronsave['1'] = acym_translation('ACYM_SIMPLIFIED_REPORT');
                $cronsave['2'] = acym_translation('ACYM_DETAILED_REPORT');

                echo acym_select(
                    $cronreportval,
                    'config[cron_savereport]',
                    (int)$this->config->get('cron_savereport', 2),
                    [
                        'class' => 'acym__select',
                        'acym-data-infinite' => '',
                    ],
                    'value',
                    'text',
                    'cronsavereport',
                    true
                );
                ?>
			</div>
			<div class="cell large-2 medium-3"><label for="cron_savepath"><?php echo acym_translation('ACYM_REPORT_SAVE_TO').acym_info('ACYM_REPORT_SAVE_TO_DESC'); ?></label></div>
			<div class="cell large-4 medium-9">
				<input id="cron_savepath" type="text" name="config[cron_savepath]" value="<?php echo acym_escape($this->config->get('cron_savepath')); ?>">
			</div>
			<div class="cell">
                <?php
                $link = acym_completeLink('cpanel', true).'&amp;task=cleanreport';
                echo '<button type="submit" data-task="deletereport" class="margin-right-1 button acy_button_submit">'.acym_translation('ACYM_REPORT_DELETE').'</button>';

                echo acym_modal(
                    acym_translation('ACYM_REPORT_SEE'),
                    '',
                    null,
                    '',
                    'class="button" data-ajax="true" data-iframe="&ctrl=configuration&task=seereport"'
                );
                ?>
			</div>
		</div>
	</div>

	<div class="acym__content acym_area padding-vertical-1 padding-horizontal-2">
		<div class="acym__title acym__title__secondary"><?php echo acym_translation('ACYM_LAST_CRON'); ?></div>
		<div class="grid-x grid-margin-x margin-y">
			<div class="cell medium-3"><?php echo acym_translation('ACYM_LAST_RUN').acym_info('ACYM_LAST_RUN_DESC'); ?></div>
			<div class="cell medium-9">
                <?php
                $cronLast = $this->config->get('cron_last', 0);
                $diff = intval((time() - $cronLast) / 60);
                if ($diff > 500) {
                    if (empty($cronLast)) {
                        echo acym_translation('ACYM_NEVER');
                    } else {
                        echo acym_date($cronLast, acym_getDateTimeFormat());
                        echo ' <span style="font-size:10px">('.acym_translationSprintf('ACYM_CURRENT_TIME', acym_date('now', acym_getDateTimeFormat())).')</span>';
                    }
                } else {
                    echo acym_translationSprintf('ACYM_MINUTES_AGO', $diff);
                }
                ?>
			</div>
			<div class="cell medium-3"><?php echo acym_translation('ACYM_CRON_TRIGGERED_IP').acym_info('ACYM_CRON_TRIGGERED_IP_DESC'); ?></div>
			<div class="cell medium-9">
                <?php echo $this->config->get('cron_fromip'); ?>
			</div>
			<div class="cell medium-3"><?php echo acym_translation('ACYM_REPORT').acym_info('ACYM_REPORT_DESC'); ?></div>
			<div class="cell medium-9">
                <?php echo nl2br($this->config->get('cron_report')); ?>
			</div>
		</div>
	</div>
	<div class="acym__content acym_area padding-vertical-1 padding-horizontal-2">
		<div class="acym__title acym__title__secondary"><?php echo acym_translation('ACYM_AUTOMATED_TASKS'); ?></div>
		<div class="grid-x grid-margin-x margin-y">
			<div class="cell acym_auto_tasks">

                <?php

                $listHours = [];
                for ($i = 0 ; $i < 24 ; $i++) {
                    $value = $i < 10 ? '0'.$i : $i;
                    $listHours[] = acym_selectOption($value, $value);
                }
                $hours = acym_select($listHours, 'config[daily_hour]', $this->config->get('daily_hour', '12'), 'class="intext_select"');

                $listMinutess = [];
                for ($i = 0 ; $i < 60 ; $i += 5) {
                    $value = $i < 10 ? '0'.$i : $i;
                    $listMinutess[] = acym_selectOption($value, $value);
                }
                $minutes = acym_select($listMinutess, 'config[daily_minute]', $this->config->get('daily_minute', '00'), 'class="intext_select"');

                echo acym_translationSprintf('ACYM_DAILY_TASKS', $hours, $minutes);
                echo acym_info('ACYM_DAILY_TASKS_DESC');

                ?>
			</div>
		</div>
	</div>
    <?php
}
if (!acym_level(ACYM_ESSENTIAL)) {
    echo '<div class="acym_area">
            <div class="acym__title acym__title__secondary">'.acym_translation('ACYM_CRON').'</div>';
    include acym_getView('configuration', 'upgrade_license');
    echo '</div>';
}
