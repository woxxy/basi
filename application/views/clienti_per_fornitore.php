<p>Clienti di oggi per il fornitore "<?php echo $fornitore['nome'] ?>" con partita IVA <?php echo $fornitore['partita_iva'] ?></p><br>

<table class="table table-bordered table-striped">
	<tbody>
		<tr><td>Clienti odierni</td><td><?php echo $result[0]['count'] ?></td></tr>
		<tr><td>Clienti odierni con allergie</td><td><?php echo $result_allergici[0]['count'] ?></td></tr>
	</tbody>
</table>