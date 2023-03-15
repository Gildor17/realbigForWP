<?php
?>

<form method="post" name="tokenForm" id="tokenFormId">
    <label><span class="element-separator" style="font-size: 16px">Токен</span><br/>
        <span style="display: flex; align-items: flex-start">
                    <input class="element-separator" name="tokenInput" id="tokenInputId" value="<?php echo $GLOBALS['token'] ?>"
                           style="min-width: 280px" required>
                </span>
    </label>
    <div class="element-separator">
        <label><input type="checkbox" name="statusRefresher" id="statusRefresher">
            обновить проверку</label>
    </div>
    <?php if (!empty($args['killRbAvailable'])): ?>
        <div class="element-separator">
            <label><input type="checkbox" name="kill_rb" id="kill_rb_id" <?php echo $args['killRbCheck'] ?>>
                Kill connection to rotator</label>
        </div>
    <?php endif; ?>
    <div class="element-separator">
        <label><input type="checkbox" name="cache_clear" id="cache_clear_id" <?php echo $args['cache_clear'] ?>>
            Очистить кэш</label>
    </div>
    <?php submit_button( 'Синхронизировать', 'primary', 'saveTokenButton' ) ?>
    <?php if (!empty($GLOBALS['tokenStatusMessage'])): ?>
        <span name="rezultDiv" style="font-size: 16px"><?php echo $GLOBALS['tokenStatusMessage'] ?></span>
    <?php endif; ?>
    <? if (!empty($GLOBALS['connection_request_rezult']) && $GLOBALS['connection_request_rezult'] != 'success'): ?>
        <div class="element-separator"><?php echo $GLOBALS['connection_request_rezult'] ?></div>
    <? endif; ?>
    <?php if (!empty($args['devMode'])): ?>
        <?php submit_button( 'Check-Ip', 'big', 'checkIp', true) ?>
        <?php if (!empty($args['curlResult'])): ?>
            <span id="ip-result"><?php echo $args['curlResult'] ?></span>
        <?php endif; ?>
    <?php endif; ?>
</form>

<?php if(!empty($GLOBALS['tokenTimeUpdate']) && $GLOBALS['tokenTimeUpdate'] != 'never'): ?>
    <div style="font-size: 16px;margin-top: 30px;">
        <div class="element-separator more" style="color: <?php echo $GLOBALS['statusColor'] ?>">
            Время последней синхронизации: <?php echo wp_date('Y-m-d H:i:s', $GLOBALS['tokenTimeUpdate']) ?></div>
        <div class="element-separator more" style="font-weight: bold">
            Время следующей втосинхронизации: <?php echo wp_date('Y-m-d H:i:s', wp_next_scheduled('rb_cron_hook')); ?></div>
    </div>
<?php endif; ?>