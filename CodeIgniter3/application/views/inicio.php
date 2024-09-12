<?php
defined('BASEPATH') or exit('No direct script access allowed');

$varController = 'biblioteca';


?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title>Welcome to CodeIgniter</title>

	<script src="../../../template/js/jquery.min.js"></script>

	<style type="text/css">
		::selection {
			background-color: #E13300;
			color: white;
		}

		::-moz-selection {
			background-color: #E13300;
			color: white;
		}

		body {
			background-color: #fff;
			margin: 40px;
			font: 13px/20px normal Helvetica, Arial, sans-serif;
			color: #4F5155;
		}

		a {
			color: #003399;
			background-color: transparent;
			font-weight: normal;
		}

		h1 {
			color: #444;
			background-color: transparent;
			border-bottom: 1px solid #D0D0D0;
			font-size: 19px;
			font-weight: normal;
			margin: 0 0 14px 0;
			padding: 14px 15px 10px 15px;
		}

		code {
			font-family: Consolas, Monaco, Courier New, Courier, monospace;
			font-size: 12px;
			background-color: #f9f9f9;
			border: 1px solid #D0D0D0;
			color: #002166;
			display: block;
			margin: 14px 0 14px 0;
			padding: 12px 10px 12px 10px;
		}

		#body {
			margin: 0 15px 0 15px;
		}

		p.footer {
			text-align: right;
			font-size: 11px;
			border-top: 1px solid #D0D0D0;
			line-height: 32px;
			padding: 0 10px 0 10px;
			margin: 20px 0 0 0;
		}

		#container {
			margin: 10px;
			border: 1px solid #D0D0D0;
			box-shadow: 0 0 8px #D0D0D0;
		}
	</style>


	<script>
		$(document).ready(function() {
			buscarProfessores();
		});


		function buscarProfessores() {
			$.ajax({
				url: "/<?php echo $varController ?>/buscarProfessores",
				dataType: 'json',
				type: 'get',
				cache: false,
				success: function(data) {
					console.log(data);
					var html = '';

					// for (var i = 0; i < data.length; i++) {
					// 	html += data[i].nome + '<br>';
					// }

					$.each(data, function(index, dado) {
						html += dado.nome + '<br>';
					});

					document.getElementById('exibirProfessores').innerHTML = html;

					$("#exibirProfessores").html('');
					$("#exibirProfessores").append(html);



				},
				error: function(d) {

					return false;
				}
			});
		}

		function salvar() {
			var name = document.getElementById('name').value;


			$.ajax({
				url: "/<?php echo $varController ?>/salvarProfessor",
				dataType: 'json',
				type: 'post',
				data: {
					name: name
				},
				cache: false,
				success: function(data) {
					console.log(data);
					alert("cadastrado com sucesso!");
					buscarProfessores();

				},
				error: function(d) {

					return false;
				}
			});
		}

		function editar() {
			var id = document.getElementById('id').value;
			var novoName = document.getElementById('novoName').value;

			$.ajax({
				url: "/<?php echo $varController ?>/editarProfessor",
				dataType: 'json',
				type: 'post',
				data: {
					id: id,
					novoName: novoName
				},
				cache: false,
				success: function(data) {
					console.log(data);
					alert("editado com sucesso!");
					buscarProfessores();

				},
				error: function(d) {

					return false;
				}
			});

		}


		function deletar() {
			var id = document.getElementById('idDeletar').value;

			$.ajax({
				url: "/<?php echo $varController ?>/deletarProfessor",
				dataType: 'json',
				type: 'post',
				data: {
					id: id
				},
				cache: false,
				success: function(data) {
					console.log(data);
					alert("deletado com sucesso!");
					buscarProfessores();

				},
				error: function(d) {

					return false;
				}
			});

		}

		function acessarTelaAlunos() {
			window.location.href = "/<?php echo $varController ?>/alunos";
		}
	</script>



</head>

<body>

	<div id="container">
		<h1>Welcome to inicio!</h1>


		<div id="exibirProfessores"></div>

		<button onclick="teste()">Teste</button>


		<label for="name">Nome:</label>
		<input type="text" id="name" name="name">

		<button onclick="salvar()">Salvar</button>
		<br><br><br>

		<label for="name">Id:</label>
		<input type="text" id="id" name="id">

		<label for="name">Novo Nome:</label>
		<input type="text" id="novoName" name="novoName">

		<button onclick="editar()">Salvar nome editado</button>

		<br><br><br>

		<label for="name">Id:</label>
		<input type="text" id="idDeletar" name="idDeletar">

		<button onclick="deletar()">Deletar</button>


		<br><br><br>

		<button onclick="acessarTelaAlunos()">Acessar tela alunos</button>

		<br>

		<a href="/<?php echo $varController ?>/alunos">Acessar tela alunos</a>




	</div>

</body>

</html>