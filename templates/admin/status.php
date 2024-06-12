<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div>
<p><textarea class="hp-copy-for-support-content" readonly="readonly"><?php echo $report['content']; ?></textarea></p>
<p><button id="hp-copy-for-support-button" class="button"><?php esc_html_e( 'Copy for support', 'hivepress' ); ?></button></p>
</div>
<?php
foreach($report['sections'] as $data):
?>
<table cellspacing="0">
	<thead>
		<tr>
			<th><h2><?php echo hivepress()->helper->get_array_value($data, 'label'); ?></h2></th>
		</tr>
	</thead>
	<tbody>
        <?php foreach(hivepress()->helper->get_array_value($data, 'values') as $value): ?>
        <tr>
            <td><?php echo hivepress()->helper->get_array_value($value, 'label'); ?></td>
            <td><?php echo hivepress()->helper->get_array_value($value, 'value'); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endforeach; ?>