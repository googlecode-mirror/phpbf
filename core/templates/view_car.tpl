{header title="View a car"}


{form data="edit_car" href="example/`$car->id`/save"}

	Name: {input name="name" value=$car->name}
	<br/>
	Price: {input name="price" value=$car->price}
	<br/>
	{input type="submit" value="Save"}
	
{/form}

{footer}
