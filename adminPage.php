<?php

if (!defined("ABSPATH")) { exit;}

try {
	if (!function_exists('RFWP_my_pl_settings_menu_create')) {
		function RFWP_my_pl_settings_menu_create() {
			if (strpos($_SERVER['REQUEST_URI'], 'page=realbigForWP')) {
				$iconUrl = plugins_url().'/'.basename(__DIR__).'/assets/realbig_plugin_hover.svg';
			} else {
				$iconUrl = plugins_url().'/'.basename(__DIR__).'/assets/realbig_plugin_standart.svg';
			}
			add_menu_page( 'Your code sending configuration', 'realBIG', 'administrator', __FILE__, 'RFWP_TokenSync', $iconUrl);
			add_action('admin_init', 'RFWP_register_mysettings');
		}
	}
	if (!function_exists('RFWP_register_mysettings')) {
		function RFWP_register_mysettings() {
			register_setting('sending_zone', 'token_value_input');
			register_setting('sending_zone', 'token_value_send' );
		}
	}
	if (!function_exists('RFWP_TokenSync')) {
		function RFWP_TokenSync() {
			global $wpdb;
			global $wpPrefix;
			global $curlResult;
			global $devMode;
			RFWP_initTestMode();

			$turboUrlTemplates = RFWP_generateTurboRssUrls();

			$blocksCounter = 1;
			$checkDirName = basename(dirname(__FILE__));
			if (!empty($GLOBALS['dev_mode'])) {
				$killRbAvailable = true;
			} else {
				$killRbAvailable = false;
			}
			$postsGatherDesktop = $wpdb->get_results('SELECT post_title FROM '.$wpPrefix.'posts WHERE post_type IN ("rb_block_desktop_new")');
			$postsGatherMobile  = $wpdb->get_results('SELECT post_title FROM '.$wpPrefix.'posts WHERE post_type IN ("rb_block_mobile_new" )');
			$getWorkProcess = $wpdb->get_var($wpdb->prepare('SELECT optionValue FROM '.$wpPrefix.'realbig_settings WHERE optionName = %s', ["work_process_status"]));
			$getBlocks = $wpdb->get_results('SELECT block_number FROM '.$wpPrefix.'realbig_plugin_settings', ARRAY_A);
			$workProcess = '';
			if (!empty($getWorkProcess)&&$getWorkProcess=='enabled') {
				$workProcess = 'checked';
            }
			if (!empty($GLOBALS['rb_rssFeedUrls'])) {
				$rb_rssFeedUrls = $GLOBALS['rb_rssFeedUrls'];
			}

			try {
				$rbSettings = $wpdb->get_results('SELECT optionName, optionValue, timeUpdate FROM ' . $GLOBALS["wpPrefix"] . 'realbig_settings WHERE optionName IN ("deactError","domain","excludedMainPage","excludedPages","pushStatus","excludedPageTypes","kill_rb")', ARRAY_A);
//			$rbTransients = $wpdb->get_results('SELECT optionName, optionValue, timeUpdate FROM ' . $GLOBALS["wpPrefix"] . 'realbig_settings WHERE optionName IN ("deactError","domain","excludedMainPage","excludedPages","pushStatus","excludedPageTypes","kill_rb")', ARRAY_A);

				$killRbCheck = '';

				if (!empty($rbSettings)) {
					foreach ($rbSettings AS $k=>$item) {
						if ($item['optionName']=='domain') {
							$usedDomain = $item["optionValue"];
						} elseif ($item['optionName']=='deactError') {
							$deacError = $item["optionValue"];
							$deacTime = $item["timeUpdate"];
						} elseif ($item['optionName']=='excludedMainPage') {
							if (!empty($item["optionValue"])) {
								$excludedMainPage = 'Да';
							} else {
								$excludedMainPage = 'Нет';
							}
						} elseif ($item['optionName']=='excludedPages') {
							$excludedPage = $item["optionValue"];
						} elseif ($item['optionName']=='excludedPageTypes'&&!empty($item["optionValue"])) {
							$excludedPageTypes = explode(',',$item["optionValue"]);
						} elseif ($item['optionName']=='pushStatus') {
							if (!empty($item["optionValue"])) {
								$pushStatus = 'Да';
							} else {
								$pushStatus = 'Нет';
							}
						} elseif ($item['optionName']=='kill_rb') {
							if (!empty($GLOBALS['dev_mode'])) {
								if (!empty($item["optionValue"])&&$item["optionValue"]==2) {
									$killRbCheck = 'checked';
								}
								if (!empty($item["optionValue"])) {
									$killRbAvailable = true;
								}
							}
						}
					}
				}

				$cache_clear = get_option('rb_cacheClearAllow');
				if (!empty($cache_clear)&&$cache_clear=='enabled') {
					$cache_clear = 'checked';
                } else {
					$cache_clear = '';
                }
			} catch (Exception $e) {
				$usedDomain = "domain gathering error";
				$deacError = "error gathering error";
				$deacTime = "error gathering error";
				$excludedMainPage = "main page gathering error";
				$excludedPage = "pages gathering error";
				$pushStatus = "error gathering error";
				$excludedPageTypes = "error gathering types";
			}
			?>
			<style>
				.separated-blocks {
					display: inline-table;
					margin-right:10px;
				}
				.element-separator {
					margin: 10px 0;
				}
				.squads-blocks {
					border: 1px solid grey;
					width: max-content;
					margin-top: 20px;
					padding: 5px;
				}
				.o-lists {
					margin: 5px 5px 5px 1em;
				}
				#folderRename {
					background-color: #B1FFB0;
				}
				#rssTest {
					background-color: #e8ff89;
					/*color: #000000;*/
				}
                #ip-result {
                    color: green;
                    font-size: 20px;
                }
			</style>
			<div class="wrap">
				<div class="separated-blocks">
					<form method="post" name="tokenForm" id="tokenFormId">
						<label><span class="element-separator" style="font-size: 16px">Токен</span><br/>
							<input class="element-separator" name="tokenInput" id="tokenInputId" value="<?php echo $GLOBALS['token'] ?>"
							       style="min-width: 280px"
							       required>
							<label class="element-separator" style="font-size: 16px; margin-left: 10px; color: <?php echo $GLOBALS['statusColor'] ?> ">Время
								последней синхронизации: <?php echo $GLOBALS['tokenTimeUpdate'] ?></label>
						</label>
						<br>
						<div class="element-separator">
							<label for="statusRefresher">обновить проверку</label>
							<input type="checkbox" name="statusRefresher" id="statusRefresher">
						</div>
						<?php if (!empty($killRbAvailable)): ?>
							<div class="element-separator">
								<label for="kill_rb">Kill connection to rotator</label>
								<input type="checkbox" name="kill_rb" id="kill_rb_id" <?php echo $killRbCheck ?>>
							</div>
						<?php endif; ?>
						<?php if (!empty($GLOBALS['rb_testMode'])): ?>
                            <div class="element-separator">
                                <label for="process_log">activate process log</label>
                                <input type="checkbox" name="process_log" id="process_log_id" <?php echo $workProcess ?>>
                            </div>
						<?php endif; ?>
                        <div class="element-separator">
                            <label for="cache_clear">Очистить кэш</label>
                            <input type="checkbox" name="cache_clear" id="cache_clear_id" <?php echo $cache_clear ?>>
                        </div>
						<br>
						<?php submit_button( 'Синхронизировать', 'primary', 'saveTokenButton' ) ?>
                        <?php if (!empty($devMode)): ?>
	                        <?php submit_button( 'Check-Ip', 'big', 'checkIp', true) ?>
	                        <?php if (!empty($curlResult)): ?>
                                <span id="ip-result"><?php echo $curlResult ?></span>
	                        <?php endif; ?>
                        <?php endif; ?>
						<?php if (!empty($GLOBALS['tokenStatusMessage'])): ?>
							<div name="rezultDiv" style="font-size: 16px"><?php echo $GLOBALS['tokenStatusMessage'] ?></div>
						<?php endif; ?>
						<?php /* if (!empty($checkDirName)&&strpos($checkDirName,'realbigForWP')!==false): ?>
							<?php submit_button('Rename', 'folderRename', 'folderRename') ?>
						<?php endif; /**/ ?>
                        <?php if (!empty($devMode)): ?>
                            <div>
                                <?php if (!empty($rb_rssFeedUrls)): ?>
                                    <?php foreach ($rb_rssFeedUrls AS $k => $item): ?>
                                        <?php if(get_option('permalink_structure')): ?>
                                            <a target="_blank" href="<?php echo home_url() ?>/feed/<?php echo $item; ?>"><?php echo home_url() ?>/feed/<?php echo $item; ?></a><br>
                                        <?php else: ?>
                                            <a target="_blank" href="<?php echo home_url() ?>/?feed=<?php echo $item; ?>"><?php echo home_url() ?>/?feed=<?php echo $item; ?></a><br>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php unset($k,$item); ?>
                                <?php endif; ?>
                                <?php // if (!empty($rssOptions['selectiveOff'])): ?>
                                <?php if(get_option('permalink_structure')): ?>
                                    <a target="_blank" href="<?php echo $turboUrlTemplates['trashRss']; ?>"><?php echo $turboUrlTemplates['trashRss']; ?></a><br>
                                <?php else: ?>
                                    <a target="_blank" href="<?php echo $turboUrlTemplates['trashRss']; ?>"><?php echo $turboUrlTemplates['trashRss']; ?></a><br>
                                <?php endif; ?>
                                <?php // endif; ?>
                            </div>
                        <?php endif; ?>
					</form>
				</div>
				<div class="separated-blocks">
					<div class="squads-blocks">
						<div>Надписи ниже нужны для тестировки</div>
						<div>Статус соединения
							1: <?php echo(!empty($GLOBALS['connection_request_rezult_1']) ? $GLOBALS['connection_request_rezult_1'] : 'empty') ?></div>
						<div>Статус соединения
							общий: <?php echo(!empty($GLOBALS['connection_request_rezult']) ? $GLOBALS['connection_request_rezult'] : 'empty') ?></div>
					</div>
					<?php if (!empty($rbSettings)): ?>
						<?php if (!empty($deacError)): ?>
							<div class="squads-blocks">
								Инфо о последней деактивации:
								<div>
									Update Time: <?php echo $deacTime?> <br>
									Error: <?php echo $deacError?> <br>
								</div>
							</div>
						<?php endif; ?>
						<?php if (!empty($usedDomain)): ?>
							<div class="squads-blocks">
								Инфо о домене:
								<div>
									Используемый домен: <span style="color: green"><?php echo $usedDomain?></span>. <br>
								</div>
							</div>
						<?php endif; ?>
						<?php if (!empty($postsGatherDesktop)||!empty($postsGatherMobile)):?>
							<div class="squads-blocks">
								Количество закешированных блоков: <?php echo count($postsGatherDesktop)+count($postsGatherMobile) ?>.<br>
								<div class="separated-blocks">
									ИД десктопных:
									<?php foreach ($postsGatherDesktop AS $item): ?>
										<div>
											<?php echo $blocksCounter++; ?>: <?php echo $item->post_title ?>;
										</div>
									<?php endforeach; ?>
								</div>
								<?php $blocksCounter = 1; ?>
								<div class="separated-blocks">
									ИД мобильных:
									<?php foreach ($postsGatherMobile AS $item): ?>
										<div>
											<?php echo $blocksCounter++; ?>: <?php echo $item->post_title ?>;
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
                        <?php if (!empty($getBlocks)): ?>
                            <div class="squads-blocks">
                                Сохранённые блоки:
                                <ol class="o-lists">
		                            <?php foreach ($getBlocks AS $item): ?>
                                        <li>
				                            <?php echo $item['block_number']; ?>;
                                        </li>
		                            <?php endforeach; ?>
                                    <?php unset($item); ?>
                                </ol>
                            </div>
                        <?php endif; ?>
						<?php if (!empty($excludedMainPage)):?>
							<div class="squads-blocks">
								Главная страница исключена: <?php echo $excludedMainPage ?>.<br>
							</div>
						<?php endif; ?>
						<?php if (!empty($excludedPage)):?>
							<div class="squads-blocks">
								Исключенные страницы: <?php echo $excludedPage ?>.<br>
							</div>
						<?php endif; ?>
						<?php if (!empty($pushStatus)):?>
							<div class="squads-blocks">
								Вставлять в хедер PUSH-код: <?php echo $pushStatus ?>.<br>
							</div>
						<?php endif; ?>
						<?php if (!empty($excludedPageTypes)):?>
							<?php $counter = 1; ?>
							<div class="squads-blocks">
								Исключенные типы страниц:
								<ol class="o-lists">
									<?php foreach ($excludedPageTypes AS $k => $item): ?>
										<li>
											<?php echo $item ?>;
										</li>
									<?php endforeach; ?>
								</ol>
							</div>
						<?php endif; ?>
                        <div class="squads-blocks">
                            Режим отладки:
                            <?php if (!empty($GLOBALS['rb_testMode'])) {
                                ?><span style="color: green;">On</span><?php
                            } else {
                                ?><span style="color: red;">Off</span><?php
                            } ?>.
                        </div>
					<?php endif; ?>
				</div>
			</div>
			<?php
		}
	}
}
catch (Exception $ex)
{
	try {
		global $wpdb;
		global $rb_logFile;

		$messageFLog = 'Deactivation error: '.$ex->getMessage().';';
		error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);

		if (!empty($GLOBALS['wpPrefix'])) {
			$wpPrefix = $GLOBALS['wpPrefix'];
		} else {
			global $table_prefix;
			$wpPrefix = $table_prefix;
		}

		$errorInDB = $wpdb->query("SELECT * FROM ".$wpPrefix."realbig_settings WHERE optionName = 'deactError'");
		if (empty($errorInDB)) {
			$wpdb->insert($wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'textEdit: '.$ex->getMessage()
			]);
		} else {
			$wpdb->update( $wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'textEdit: '.$ex->getMessage()
			], ['optionName'  => 'deactError']);
		}
	} catch (Exception $exIex) {
	} catch (Error $erIex) { }

	deactivate_plugins(plugin_basename( __FILE__ ));
	?><div style="margin-left: 200px; border: 3px solid red"><?php echo $ex; ?></div><?php
}
catch (Error $er)
{
	try {
		global $wpdb;
		global $rb_logFile;

		$messageFLog = 'Deactivation error: '.$er->getMessage().';';
		error_log(PHP_EOL.current_time('mysql').': '.$messageFLog.PHP_EOL, 3, $rb_logFile);

		if (!empty($GLOBALS['wpPrefix'])) {
			$wpPrefix = $GLOBALS['wpPrefix'];
		} else {
			global $table_prefix;
			$wpPrefix = $table_prefix;
		}

		$errorInDB = $wpdb->query("SELECT * FROM ".$wpPrefix."realbig_settings WHERE optionName = 'deactError'");
		if (empty($errorInDB)) {
			$wpdb->insert($wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'textEdit: '.$er->getMessage()
			]);
		} else {
			$wpdb->update( $wpPrefix.'realbig_settings', [
				'optionName'  => 'deactError',
				'optionValue' => 'textEdit: '.$er->getMessage()
			], ['optionName'  => 'deactError']);
		}
	} catch (Exception $exIex) {
	} catch (Error $erIex) { }

	deactivate_plugins(plugin_basename( __FILE__ ));
	?><div style="margin-left: 200px; border: 3px solid red"><?php echo $er; ?></div><?php
}