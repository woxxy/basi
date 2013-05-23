<form action="<?php echo current_url() ?>"
	<fieldset>
		<label>Nome scuola</label>
		<input type="text" name="nome_scuola" value="<?php echo $this->input->get('nome_scuola'); ?>" placeholder="Nome scuola">
		<label>Circoscrizione scuola</label>
		<input type="text" name="circoscrizione_scuola" value="<?php echo $this->input->get('circoscrizione_scuola'); ?>" placeholder="Circoscrizione scuola">
		<br>
		<input type="submit" value="Invia" class="btn btn-primary">
	</fieldset>
</form>
<hr>
<table class="table table-striped table-bordered">
	<thead>
		<th>Nome Scuola</th>
		<th>Circoscrizione</th>
		<th>Indirizzo</th>
	</thead>
	<tbody>
		<?php foreach ($scuole as $scuola) : ?>
		<tr>
			<td><?php echo $scuola['nome'] ?></td>
			<td><?php echo $scuola['circoscrizione'] ?></td>
			<td><?php echo $scuola['indirizzo'] ?></td>
			<td><button class="btn btn-success" data-action="fill_form_nome_circoscrizione" data-nome="<?php echo htmlspecialchars($scuola['nome']) ?>" data-circoscrizione="<?php echo htmlspecialchars($scuola['nome']) ?>">Inserisci</button></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>