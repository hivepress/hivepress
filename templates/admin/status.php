<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div>
<p><textarea class="hp-copy-for-support-content" readonly="readonly"><?php echo $report['content']; ?></textarea></p>
<p><button id="hp-copy-for-support-button" class="button"><?php esc_html_e( 'Copy for support', 'hivepress' ); ?></button></p>
</div>
<table class="hp-status-table" cellspacing="0">
<?php
foreach($report['sections'] as $data):
?>
	<tbody>
        <tr>
			<th colspan="2"><h2><?php echo hivepress()->helper->get_array_value($data, 'label'); ?></h2></th>
		</tr>
        <?php foreach(hivepress()->helper->get_array_value($data, 'values') as $value): ?>
        <tr>
            <?php if(hivepress()->helper->get_array_value($value, 'url')): ?>
            <td class="hp-status-table--option-label"><a href="<?php echo esc_attr(hivepress()->helper->get_array_value($value, 'url')); ?>" target="_blank"><?php echo hivepress()->helper->get_array_value($value, 'label'); ?></a></td>
            <?php else: ?>
            <td><?php echo hivepress()->helper->get_array_value($value, 'label'); ?></td>
            <?php endif; ?>
            <td><?php echo hivepress()->helper->get_array_value($value, 'value'); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
<?php endforeach; ?>
</table>