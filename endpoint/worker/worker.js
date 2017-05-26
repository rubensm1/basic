function EhPrimo(num)
{
	var i;
	if(num == 2)
		return true;
	if(num&1 == 0)
		return false;
	rnum = Math.pow(num, 1/2)
	for(i = 3; i <= rnum; i = i + 2)
	{
		if(num%i == 0)
			return false;
	}
	return true;
}
function Exec(num)
{
	var limite = parseInt(num);
	var primos = [];
	if(limite >= 2)
		primos.push(2);
	for (var i = 3; i <= limite; i = i + 2)
		if(EhPrimo(i))
			primos.push(i);
	postMessage(primos.join(", "));
}



self.addEventListener('message', function(e) {
	var num = e.data["num"] ? e.data["num"] : 0;
	if (num)
		Exec(num);
}, false);

postMessage("");
