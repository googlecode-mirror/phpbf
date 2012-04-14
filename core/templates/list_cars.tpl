{header title="List of cars"}

List of cars:

<br/>
<ul>
{foreach $list as $car}
	<li>{$car->name} at {$car->price} {a href="example/`$car->id`/view"}[View]{/a}</li>
{foreachelse}
	No cars available
{/foreach}
</ul>

{footer}
