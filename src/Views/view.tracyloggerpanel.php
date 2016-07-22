<style class="tracy-debug">

	#tracy-debug .fuzeworks-LoggerPanel table {
		font: 9pt/1.5 Consolas, monospace;
	}

	#tracy-debug .fuzeworks-LoggerPanel .error td {
		background: #FF3300 !important;
	}

	#tracy-debug .fuzeworks-LoggerPanel .warning td {
		background: #FFFF66 !important;
	}

	#tracy-debug .fuzeworks-LoggerPanel .debug td {
		background: #33CC33 !important;
	}

	#tracy-debug .fuzeworks-LoggerPanel .info td {
		background: #BDE678 !important;
	}

	#tracy-debug .fuzeworks-LoggerPanel .cinfo td {
		background: #BDE622 !important;
	}

	#tracy-debug .fuzeworks-LoggerPanel pre, #tracy-debug .fuzeworks-LoggerPanel code {
		display: inline;
		background: transparent;
	}

</style>

<div class="fuzeworks-LoggerPanel">
<h1> FuzeWorks - Logger</h1>

<div class="tracy-inner">
	<table>
	<thead>
	<tr>
		<th>Type</th>
		<th>Message</th>
		<th>File</th>
		<th>Line</th>
		<th>Timing</th>
	</tr>
	</thead>

	<tbody>
	<?php foreach ($logs as $log): ?>
		<?php if ($log['type'] === 'LEVEL_STOP')
		{
			continue;
		}
		elseif ($log['type'] === 'LEVEL_START')
		{
			$log['type'] = 'CINFO';
		}
		?>
	<tr class="<?php echo($log['type']); ?>">
		<td><?php echo(  htmlspecialchars ($log['type'])); ?></td>
		<td><?php echo(  htmlspecialchars ($log['message'])); ?></td>
		<td><?php echo( empty($log['logFile']) ? 'x' : htmlspecialchars ($log['logFile'])); ?></td>
		<td><?php echo( empty($log['logLine']) ? 'x' : htmlspecialchars ($log['logLine'])); ?></td>
		<td><?php echo(round($log['runtime'] * 1000, 4)); ?> ms</td>
	</tr>
	<?php endforeach ?>
	</tbody>
	</table>
</div>
</div>
