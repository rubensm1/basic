var View;

View = (function () {

    function View() {
    }

    View.prototype.carregar = function (lista, classe) {
		if (classe == null)
			classe = this.constructor.name;
		for (var i in lista) {
			lista[i] = eval("new " + classe + "(lista[i])");
		}
		return lista;
    };

	View.prototype.formatar = function (dado) {
		if (typeof dado == "boolean")
			return dado ? "Sim" : "Não";
		if (dado instanceof Date)
			return dado.toLocaleDateString();
		return dado;
	}
	
    View.prototype.htmlTable = function (lista) {
		var html = '<table class="table table-bordered"><thead><tr>';
		var h = false;
		for (var i in lista) {
			if (h) {
				html += "<tr>";
				for (var k in lista[i]) {
					if (typeof lista[i][k] == "function")
						continue;
					html += "<td>" + this.formatar(lista[i][k]) + "</td>";
				}
				html += "</tr>";
			}
			else {
				var html2 = "<tbody><tr>";
				for (var k in lista[i]) {
					if (typeof lista[i][k] == "function")
						continue;
					html += "<th>" + (k == "id" ? "<u>" + k + "</u>" : k) + "</th>";
					html2 += "<td>" + this.formatar(lista[i][k]) + "</td>";
				}
				html += "</tr></thead>" + html2 + "</tr>";
				h = true;
			}
		}
		return html + "</tbody></table>";
    };

    return View;
})();