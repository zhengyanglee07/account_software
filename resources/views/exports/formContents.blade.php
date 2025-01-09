<table>
    <thead>
		<tr>
			@foreach($formLabels as $labelId => $labelName)
				<th>{{ $labelName }}</th>
			@endforeach
			<th>Submitted At</th>
		</tr>
	</thead>
	<tbody>
		@foreach($formContents as $formContent)
			<tr>
				@foreach($formLabels as $labelId => $labelName)
					<td>
						{{ $formContent[$labelId] ?? '' }}
					</td>
				@endforeach
				<td>{{ $formContent['Submitted At'] }}</td>
			</tr>
		@endforeach
	</tbody>
</table>