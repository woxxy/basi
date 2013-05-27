<table class="table table-striped table-bordered">
	<tbody>
		<?php foreach ($tabella as $t) : ?>
			<tr>
				<td><?php echo $t[0] ?></td>

				<td><?php echo $t[1] ?> </td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>