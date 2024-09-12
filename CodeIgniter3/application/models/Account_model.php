<?php
class Account_model extends CI_Model
{
	//Trata os caracteres para utf-8, tanto os de entrada como os de sa�da de dados.
	function __construct()
	{
		$this->db->query("SET NAMES 'utf8'");
	}

	function verificarConta($dadosAccount)
	{
		$this->db->select('id as id_account, email as email_account, name as name_account');
		$this->db->from('accounts');
		$this->db->where('name', $dadosAccount['name']);
		$this->db->where('password', $dadosAccount['password']);
		return $this->db->get()->row_array();
	}

	function verificarEmail($email)
	{
		$this->db->select('id');
		$this->db->from('accounts');
		$this->db->where('email', $email);
		return $this->db->get()->row_array();
	}

	function verificarNamePlayer($namePlayer)
	{
		$this->db->select('id');
		$this->db->from('players');
		$this->db->where('name', $namePlayer);
		$id = $this->db->get()->row_array();
		if ($id == null) {
			return null;
		} else {
			return true;
		}
	}

	function verificarNameAccount($nameAccount)
	{
		$this->db->select('id');
		$this->db->from('accounts');
		$this->db->where('name', $nameAccount);
		$id = $this->db->get()->row_array();
		if ($id == null) {
			return null;
		} else {
			return true;
		}
	}

	function verificarEmailAccount($EmailAccount)
	{
		$this->db->select('id');
		$this->db->from('accounts');
		$this->db->where('email', $EmailAccount);
		$id = $this->db->get()->row_array();
		if ($id == null) {
			return null;
		} else {
			return true;
		}
	}

	function criarConta($dadosAccount, $dadosPlayer)
	{
		$this->db->trans_start(); //inicio transa��o

		// $this->db->set('name', $dadosAccount['name']);
		// $this->db->set('password', $dadosAccount['password']);
		// $this->db->set('email', $dadosAccount['email']);
		$this->db->insert('accounts', $dadosAccount);
		$account_id = $this->db->insert_id(); //id do insert 

		// $this->db->set('name', $dadosPlayer['name']);
		// $this->db->set('image', $dadosPlayer['image']);
		// 
		// $this->db->set('sex', $dadosPlayer['sex']);
		$this->db->set('account_id', $account_id);
		$this->db->insert('players', $dadosPlayer); //insert marca��o 
		$player_id = $this->db->insert_id();
		
		//quando cria a conta ele pega os items daquele player id ali 18331

		$this->db->select('*');
		$this->db->from('player_items');
		$this->db->where('player_id', 18331);
		$dados = $this->db->get()->result_array();

		for ($i = 0; $i < count($dados); $i++) {
			$dados[$i]['player_id'] = $player_id;
			$this->db->insert('player_items', $dados[$i]);
		}

		$this->db->trans_complete(); //fim transa��o

		if ($this->db->trans_status() === FALSE) {
			return false;
		} else {
			return $account_id;
		}
	}

	function criarPersonagem($dadosPlayer)
	{
		$this->db->trans_start(); //inicio transa��o

		$this->db->insert('players', $dadosPlayer); //insert
		$player_id = $this->db->insert_id();

		$this->db->select('*');
		$this->db->from('player_items');
		$this->db->where('player_id', 18331);
		$dados = $this->db->get()->result_array();

		for ($i = 0; $i < count($dados); $i++) {
			$dados[$i]['player_id'] = $player_id;
			$this->db->insert('player_items', $dados[$i]);
		}

		$this->db->trans_complete(); //fim transa��o

		if ($this->db->trans_status() === FALSE) {
			return false;
		} else {
			return true;
		}
	}

	function buscarDadosConta()
	{
		$this->db->select('name, image, email, chave_pix');
		$this->db->from('accounts');
		$this->db->where('id', $this->encryption->decrypt($this->input->cookie('hand_idAccount')));
		return $this->db->get()->row_array();
	}

	function buscarPersonagens()
	{
		$this->db->select('players.id as id_player, players.name as name_player, players.level, players.sex, players.image, players.looktype,
		players.lookaddons, players.lookhead, players.lookbody, players.looklegs, players.lookfeet, guilds.name as guild_name,
		players.created, players.lastlogout, players.online, player_seller.price');
		$this->db->from('players');
		$this->db->where('account_id', $this->encryption->decrypt($this->input->cookie('hand_idAccount')));
		$this->db->join('guild_ranks', 'guild_ranks.id = players.rank_id', 'left');
		$this->db->join('guilds', 'guilds.id = guild_ranks.guild_id', 'left');
		$this->db->join('player_seller', 'player_seller.char_id = players.id', 'left');
		$dados =  $this->db->get()->result_array();
		$listaQuests = $this->listarQuests();
		foreach ($dados as $key => $value) {
			foreach ($listaQuests as $key2 => $value2) {
				$listaQuests[$key2]['value'] = $this->buscarQuestesPlayer($value['id_player'], $value2['storage']);
			}
			$dados[$key]['quests'] = $listaQuests;

			$storages = $this->buscarDexPlayer($value['id_player']);

			$dados[$key]['goals'] = $this->buscarGoalsPlayer($value['id_player']);

			$pokemonsNaBag = array();

			// if ($value['pokemons'] != null && $value['pokemons'] != '') {
			// 	$dataArray = json_decode($value['pokemons'], true);
			// 	foreach ($dataArray as $key4 => $value4) {
			// 		if (!empty($value4['name'])) {
			// 			$poke =  explode(" ",  $value4['name']);
			// 			if ($poke[0] == 'Shiny') {
			// 				$sh = 'shiny';
			// 				$nomeDoPoke = $poke[1];
			// 			} else {
			// 				$sh = 'normal';
			// 				$nomeDoPoke = $poke[0];
			// 			}
			// 			$pokeDex = $this->buscarNomePokeDex($nomeDoPoke);
			// 			if ($pokeDex != null) {
			// 				$pokemonsNaBag[] = array(
			// 					'img' => 'assets/pokeDex/' . $sh . '/' . $pokeDex['wild_id'] . '.gif',
			// 					'nome' => $value4['name']
			// 				);
			// 			}
			// 		}
			// 	}
			// }

			$dados[$key]['pokeBag'] = $pokemonsNaBag;

			$DexPokemons = array();

			foreach ($storages as $key3 => $value3) {
				$exposed = explode(" ", $value3['value']);
				$nome = explode(":",  $value3['value']);
				if (in_array("dex,", $exposed)) {
					$poke =  explode(" ",  $nome[0]);
					if ($poke[0] == 'Shiny') {
						$sh = 'shiny';
						$nomeDoPoke = $poke[1];
					} else {
						$sh = 'normal';
						$nomeDoPoke = $poke[0];
					}
					$pokeDex = $this->buscarNomePokeDex($nomeDoPoke);
					if ($pokeDex != null) {
						$DexPokemons[] = array(
							'img' => 'assets/pokeDex/' . $sh . '/' . $pokeDex['wild_id'] . '.gif',
							'nome' => $nome[0]
						);
					}
				}
			}
			$dados[$key]['pokeDex'] = $DexPokemons;


			$dados[$key]['itensVender'] = $this->buscarItensPermitidos();
			$dados[$key]['itensAvenda'] = $this->buscarItensAVenda($value['id_player']);


		}
		return $dados;
	}

	function buscarItensPermitidos()
	{
		$this->db->select('id, name');
		$this->db->from('itens_permitidos');
		$this->db->where('status', 'T');
		return $this->db->get()->result_array();
	}

	function buscarItensAVenda($idPlayer)
	{
		$this->db->select('item_seller.count, item_seller.id, item_seller.price, itens_permitidos.name, itens_permitidos.image');
		$this->db->from('item_seller');
		$this->db->join('itens_permitidos', 'itens_permitidos.itemtype = item_seller.itemtype', 'left');
		$this->db->where('item_seller.char_id', $idPlayer);
		return $this->db->get()->result_array();
	}

	function buscarPlayer($namePlayer)
	{
		$this->db->select('players.id as id_player, players.name as name_player, players.level, players.sex, players.image, players.looktype,
		players.lookaddons, players.lookhead, players.lookbody, players.looklegs, players.lookfeet, guilds.name as guild_name,
		players.created, players.lastlogout, players.online, players.pokemons');
		$this->db->from('players');
		$this->db->where('players.name', $namePlayer);
		$this->db->join('guild_ranks', 'guild_ranks.id = players.rank_id', 'left');
		$this->db->join('guilds', 'guilds.id = guild_ranks.guild_id', 'left');
		$dados =  $this->db->get()->result_array();
		$listaQuests = $this->listarQuests();
		foreach ($dados as $key => $value) {
			foreach ($listaQuests as $key2 => $value2) {
				$listaQuests[$key2]['value'] = $this->buscarQuestesPlayer($value['id_player'], $value2['storage']);
			}
			$dados[$key]['quests'] = $listaQuests;

			$storages = $this->buscarDexPlayer($value['id_player']);

			$pokemonsNaBag = array();

			if ($value['pokemons'] != null && $value['pokemons'] != '') {
				$dataArray = json_decode($value['pokemons'], true);
				foreach ($dataArray as $key4 => $value4) {
					if (!empty($value4['name'])) {
						$poke =  explode(" ",  $value4['name']);
						if ($poke[0] == 'Shiny') {
							$sh = 'shiny';
							$nomeDoPoke = $poke[1];
						} else {
							$sh = 'normal';
							$nomeDoPoke = $poke[0];
						}
						$pokeDex = $this->buscarNomePokeDex($nomeDoPoke);
						if ($pokeDex != null) {
							$pokemonsNaBag[] = array(
								'img' => 'assets/pokeDex/' . $sh . '/' . $pokeDex['wild_id'] . '.gif',
								'nome' => $value4['name']
							);
						}
					}
				}
			}
			$dados[$key]['pokeBag'] = $pokemonsNaBag;

			$DexPokemons = array();

			foreach ($storages as $key3 => $value3) {
				$exposed = explode(" ", $value3['value']);
				$nome = explode(":",  $value3['value']);
				if (in_array("dex,", $exposed)) {
					$poke =  explode(" ",  $nome[0]);
					if ($poke[0] == 'Shiny') {
						$sh = 'shiny';
						$nomeDoPoke = $poke[1];
					} else {
						$sh = 'normal';
						$nomeDoPoke = $poke[0];
					}
					$pokeDex = $this->buscarNomePokeDex($nomeDoPoke);
					if ($pokeDex != null) {
						$DexPokemons[] = array(
							'img' => 'assets/pokeDex/' . $sh . '/' . $pokeDex['wild_id'] . '.gif',
							'nome' => $nome[0]
						);
					}
				}
			}
			$dados[$key]['pokeDex'] = $DexPokemons;
		}
		return $dados;
	}

	function buscarQuestesPlayer($player_id, $key)
	{
		$this->db->select('value');
		$this->db->from('player_storage');
		$this->db->where('player_storage.key', $key);
		$this->db->where('player_storage.player_id', $player_id);
		$value = $this->db->get()->row_array();
		if ($value == null) {
			return 0;
		} else {
			return $value['value'];
		}
	}

	function buscarDexPlayer($player_id)
	{
		$this->db->select('value');
		$this->db->from('player_storage');
		$this->db->where('player_storage.player_id', $player_id);
		return $this->db->get()->result_array();
	}

	function buscarGoalsPlayer($player_id)
	{
		$this->db->select('valor');
		$this->db->from('historico_pagamentos');
		$this->db->where('player_id', $player_id);
		$this->db->where('account_id', $this->encryption->decrypt($this->input->cookie('hand_idAccount')));
		$this->db->where('status', 4);
		$this->db->where('entregue', 1);
		$this->db->where('date_created >=', DATA_INICIO_GOALS);
		$this->db->where('date_created <=', DATA_FIM_GOALS);
		$valores = $this->db->get()->result_array();

		$valorTotal = 0;
		foreach ($valores as $key => $value) {
			$valorTotal += $value['valor'];
		}

		return $valorTotal;
	}

	function buscarNomePokeDex($name)
	{
		$this->db->select('*');
		$this->db->from('pokedex');
		$this->db->where('name', $name);
		return $this->db->get()->row_array();
	}

	function listarQuests()
	{
		$this->db->select('id, name, descricao, storage');
		$this->db->from('config_quests');
		return $this->db->get()->result_array();
	}

	function buscarHistoricoMP()
	{
		$this->db->select('*');
		$this->db->from('historico_pagamentos');
		$this->db->where('account_id', $this->encryption->decrypt($this->input->cookie('hand_idAccount')));
		$this->db->where('status', 1);
		$this->db->where('tipo', 'MercadoPago');
		return $this->db->get()->result_array();
	}

	function buscarHistoricoMPpix($payment_id)
	{
		$this->db->select('*');
		$this->db->from('historico_pagamentos');
		$this->db->where('account_id', $this->encryption->decrypt($this->input->cookie('hand_idAccount')));
		$this->db->where('payment_id', $payment_id);
		$this->db->where('status', 1);
		$this->db->where('tipo', 'MercadoPago');
		return $this->db->get()->row_array();
	}

	function inserirHistoricoPagamentoMP($payment_id, $valor, $multiplicador, $promocional_id, $player_id)
	{
		$this->db->set('payment_id', $payment_id);
		$this->db->set('account_id', $this->encryption->decrypt($this->input->cookie('hand_idAccount')));
		$this->db->set('player_id', $player_id);
		$this->db->set('valor', $valor);
		$this->db->set('multiplicador', $multiplicador);
		$this->db->set('promocional_id', $promocional_id);
		$this->db->set('tipo', 'MercadoPago');
		$this->db->insert('historico_pagamentos'); //insert marca��o marcacao_consultas_procedimentos
	}

	function inserirHistoricoPagamentoMPpixVindoDoTibia($id_account, $payment_id, $valor, $multiplicador, $promocional_id)
	{
		$this->db->set('payment_id', $payment_id);
		$this->db->set('account_id', $id_account);
		$this->db->set('valor', $valor);
		$this->db->set('multiplicador', $multiplicador);
		$this->db->set('promocional_id', $promocional_id);
		$this->db->insert('historico_mp'); //insert marca��o marcacao_consultas_procedimentos
	}

	function entregarPontos($dadosHistorico, $status, $aprovado = false)
	{
		if ($aprovado && $status == 'approved') {
			$this->db->trans_start(); //inicio transa��o

			$this->db->select('id as id_player, email, premium_points');
			$this->db->from('accounts');
			$this->db->where('id', $dadosHistorico['account_id']);
			$dadosPlayer = $this->db->get()->row_array();

			$this->db->set('status', 4);
			$this->db->set('entregue', 1);
			$this->db->where('id', $dadosHistorico['id']);
			$this->db->where('payment_id', $dadosHistorico['payment_id']);
			$this->db->update('historico_pagamentos');

			if ($dadosHistorico['promocional_id'] != null) {

				$this->db->select('*');
				$this->db->from('config_promocional');
				$this->db->where('id', $dadosHistorico['promocional_id']);
				$dadosCodigoPromocional = $this->db->get()->row_array();

				$pontosaReceberSemPorcentagemAplicadaComMultiplicador = $dadosHistorico['valor'] * $dadosHistorico['multiplicador'];
				$pontosaReceberDaPorcentagem = round($pontosaReceberSemPorcentagemAplicadaComMultiplicador * ($dadosCodigoPromocional['porcentagem'] / 100));
				$pontosTotal = $pontosaReceberSemPorcentagemAplicadaComMultiplicador + $pontosaReceberDaPorcentagem;

				$this->db->set('premium_points', $pontosTotal + $dadosPlayer['premium_points']);
				$this->db->where('id', $dadosHistorico['account_id']);
				$this->db->update('accounts');

				$this->db->select('id as id_player, email, premium_points');
				$this->db->from('accounts');
				$this->db->where('id', $dadosCodigoPromocional['id_account']);
				$dadosPlayerPromocional = $this->db->get()->row_array();

				$pontosAseremDadosAaccountPromocional = round($dadosHistorico['valor'] * ($dadosCodigoPromocional['porcentagem'] / 100));

				$this->db->set('premium_points', $pontosAseremDadosAaccountPromocional + $dadosPlayerPromocional['premium_points']);
				$this->db->where('id', $dadosCodigoPromocional['id_account']);
				$this->db->update('accounts');
			} else {
				$this->db->set('premium_points', ($dadosHistorico['valor'] * $dadosHistorico['multiplicador']) + $dadosPlayer['premium_points']);
				$this->db->where('id', $dadosHistorico['account_id']);
				$this->db->update('accounts');
			}



			$this->db->trans_complete(); //fim transa��o

			if ($this->db->trans_status() === FALSE) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	function atualizarStatusPagamento($dadosHistorico, $status_id)
	{
		$this->db->set('status', $status_id);
		$this->db->where('id', $dadosHistorico['id']);
		$this->db->where('payment_id', $dadosHistorico['payment_id']);
		$this->db->update('historico_pagamentos');
	}

	function buscarPontosPlayer()
	{
		$this->db->select('premium_points');
		$this->db->from('accounts');
		$this->db->where('id', $this->encryption->decrypt($this->input->cookie('hand_idAccount')));
		return $this->db->get()->row_array();
	}

	function buscarConfigPayment()
	{
		$this->db->select('*');
		$this->db->from('config_payment');
		$this->db->where('status', 'T');
		return $this->db->get()->row_array();
	}

	function buscarConfigPaymentMultiplicador()
	{
		$this->db->select('multiplicador');
		$this->db->from('config_payment');
		$this->db->where('status', 'T');
		return $this->db->get()->row_array();
	}

	function buscarDownloads()
	{
		$this->db->select('pc, mobile32, mobile64, img_pc, img_mobile_32,  img_mobile_64');
		$this->db->from('config_inicio');
		$this->db->where('status', 'T');
		return $this->db->get()->row_array();
	}

	function buscarEquipe()
	{
		$this->db->select('apelido, avatar, cargo');
		$this->db->from('config_team');
		$this->db->where('status', 'T');
		return $this->db->get()->result_array();
	}

	function verificarEmailToken($email)
	{
		$this->db->select('id');
		$this->db->from('accounts');
		$this->db->where('email', $email);
		$dados = $this->db->get()->row_array();

		if ($dados == null) {
			return false;
		} else {
			return true;
		}
	}

	function verificarToken($email, $token)
	{
		$this->db->select('id');
		$this->db->from('accounts');
		$this->db->where('email', $email);
		$dados = $this->db->get()->row_array();

		$this->db->select('token');
		$this->db->from('tokenvalidat');
		$this->db->where('id_account', $dados['id']);
		$this->db->where('token', $token);
		$this->db->where('expired', 'F');
		$dadosToken = $this->db->get()->row_array();

		if ($dadosToken == null) {
			return false;
		} else {
			return true;
		}
	}

	function buscarIdAccount($email, $gerarSenha = null)
	{
		if ($gerarSenha == 'T') {
			$this->db->select('name, id');
			$this->db->from('accounts');
			$this->db->where('email', $email);
			return $this->db->get()->row_array();
		} else {
			return false;
		}
	}

	function novaSenha($idAccount, $novaSenha, $gerarSenha)
	{
		if ($gerarSenha == 'T') {
			$this->db->set('password', $novaSenha);
			$this->db->where('id', $idAccount);
			return $this->db->update('accounts');
		} else {
			return false;
		}
	}

	function guardarToken($email, $token)
	{
		$this->db->trans_start(); //inicio transa��o

		$this->db->select('id');
		$this->db->from('accounts');
		$this->db->where('email', $email);
		$dadosAccount = $this->db->get()->row_array();

		$this->db->query("CALL InsertOrUpdateToken(" . $dadosAccount['id'] . ", '" . $token . "')");

		$this->db->trans_complete(); //fim transa��o

		if ($this->db->trans_status() === FALSE) {
			return false;
		} else {
			return true;
		}
	}

	function alterarPerfil($dadosAccount)
	{
		if ($dadosAccount) {
			$this->db->where('id', $this->encryption->decrypt($this->input->cookie('hand_idAccount')));
			return $this->db->update('accounts', $dadosAccount);
		} else {
			return true;
		}
	}

	function verificarSenhaAntiga($senhaAntiga)
	{
		$this->db->select('id');
		$this->db->from('accounts');
		$this->db->where('id', $this->encryption->decrypt($this->input->cookie('hand_idAccount')));
		$this->db->where('password', sha1($senhaAntiga));
		$dados = $this->db->get()->row_array();

		if ($dados == null) {
			return false;
		} else {
			return true;
		}
	}

	function verificarCodigo($codigo)
	{
		$this->db->select('*');
		$this->db->from('config_promocional');
		$this->db->where('codigo', $codigo);
		$this->db->where('status', 'T');
		$dados = $this->db->get()->row_array();

		if ($dados == null) {
			return null;
		} else if ($dados['id_account'] == $this->encryption->decrypt($this->input->cookie('hand_idAccount'))) {
			return false;
		} else {
			return $dados;
		}
	}

	function buscarCategoriasWiki()
	{
		$this->db->select('config_categoria_wiki.*');
		$this->db->from('config_categoria_wiki');
		$this->db->where('config_categoria_wiki.status', 'T');
		return $this->db->get()->result_array();
	}

	function buscarTotalWikisPorCategoria($idCategoria)
	{
		$this->db->where('categoria_id', $idCategoria);
		$this->db->where('status', 'T');
		return $this->db->count_all_results('config_wiki');
	}

	function buscarWikisPorCategoria($idCategoria)
	{
		$this->db->select('id, titulo');
		$this->db->from('config_wiki');
		$this->db->where('categoria_id', $idCategoria);
		$this->db->where('status', 'T');
		return $this->db->get()->result_array();
	}

	function buscarWikis($id)
	{
		$this->db->select('*');
		$this->db->from('config_wiki');
		$this->db->where('id', $id);
		return $this->db->get()->row_array();
	}

	function buscarAccount($nameAccount)
	{
		$this->db->where('id');
		$this->db->from('account');
		$this->db->where('name', $nameAccount);
		return $this->db->get()->row_array();
	}

	public function total_suportes($status = '')
	{
		$this->db->where('suporte.status', $status);
		$this->db->where('suporte.account_id', $this->encryption->decrypt($this->input->cookie('hand_idAccount')));
		return $this->db->count_all_results('suporte');
	}

	function buscarSuportes($inicio, $maximo, $status = '')
	{
		$this->db->select("suporte.id, suporte.titulo, suporte.status, date_format(suporte.date_created, '%d/%m/%Y') as data_created, date_format(suporte.date_update, '%d/%m/%Y') as data_update, config_team.apelido");
		$this->db->from('suporte');
		$this->db->join('config_team', 'suporte.update_admin_id = config_team.id_account', 'left');
		$this->db->where('suporte.status', $status);
		$this->db->where('suporte.account_id', $this->encryption->decrypt($this->input->cookie('hand_idAccount')));
		$this->db->limit($maximo, $inicio);
		return $this->db->get()->result_array();
	}

	function cadastrarSuporte($dadosSuporte)
	{
		return $this->db->insert('suporte', $dadosSuporte);
	}

	function buscarDadosSuporte($idSuporte)
	{
		$this->db->select('suporte.account_id, suporte.titulo, suporte.descricao, suporte.status, suporte.image1, suporte.image2');
		$this->db->from('suporte');
		$this->db->where('suporte.id', $idSuporte);
		$this->db->where('suporte.account_id', $this->encryption->decrypt($this->input->cookie('hand_idAccount')));
		return $this->db->get()->row_array();
	}

	function buscarConversasSuporte($idSuporte)
	{
		$this->db->select("suporte_respostas.id, suporte_respostas.resposta, date_format(suporte_respostas.date_created, '%d/%m/%Y') as data_created, config_team.apelido, accounts.name");
		$this->db->from('suporte_respostas');
		$this->db->join('config_team', 'suporte_respostas.admin_id = config_team.id_account', 'left');
		$this->db->join('accounts', 'suporte_respostas.account_id = accounts.id', 'left');
		$this->db->where('suporte_respostas.suporte_id', $idSuporte);
		$this->db->order_by('suporte_respostas.date_created', 'ASC');
		return $this->db->get()->result_array();
	}

	function enviarMensagemSuporte($dadosSuporte)
	{
		return $this->db->insert('suporte_respostas', $dadosSuporte);
	}

	function verificarMaxPlayerAccount()
	{
		$this->db->where('account_id', $this->encryption->decrypt($this->input->cookie('hand_idAccount')));
		return $this->db->count_all_results('players');
	}
	
	function deletarPersonagem($idPlayer)
	{
		$this->db->where('id', $idPlayer);
		$this->db->where('account_id', $this->encryption->decrypt($this->input->cookie('hand_idAccount')));
		return $this->db->delete('players');
	}

}
