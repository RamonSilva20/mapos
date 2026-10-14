# Teste do PR #2981 (achados 9 e 11 da #2879) - commit ccb0cced, base v5 12afd39a

Resultado: REPROVADO por 1 achado (API de usuário ainda vaza existência de e-mail pelo tempo). Sem merge.

1. Migration up/down/up: OK. Sem a coluna, login, navegação e troca de senha funcionam; com ela, sessao_versao=0 por padrão.
2. Duas sessões: troca da própria senha derruba a outra e mantém a atual; troca pelo admin em Usuários derruba todas as sessões do usuário; edição da própria conta em Usuários mantém a atual e derruba a outra. Mensagem "A senha desta conta foi alterada." no login. OK.
3. Desativar, expirar, desativar grupo, excluir: a sessão cai na próxima requisição (100-250 ms) com a mensagem de cada caso. Grupo que exige 2FA leva a /mapos/doisFatores em qualquer rota. OK (ressalva A).
4. confirmacao: 5 erros e a 6ª (senha certa) bloqueia em alterar senha, novos códigos e desativar 2FA; login normal segue funcionando; após a janela a senha certa passa. OK (ressalva B).
5. Recuperação de senha na área do cliente: 7 pedidos com e-mail existente e inexistente dão resposta idêntica; 5 tokens e 5 e-mails na fila para o existente, 0 para o inexistente; fluxo de reset válido e login com a nova senha OK. (Sem emitente cadastrado a resposta para e-mail existente difere: é pré-existente, depende do emitente.)
6. Logins painel, área do cliente, API usuário, API cliente e TOTP: funcionam, mensagem idêntica. Tempo (12 amostras cada, senha errada, ver timing.txt): painel 852/836 ms, área do cliente 832/838 ms, API cliente 807/803 ms (existente/inexistente), MAS API usuário 837 ms existente x 17 ms inexistente. FALHA: UsuariosController::login_post usa `if ($user && senhaConfere(...))`, que não chama senhaConfere sem usuário. Navegação: mediana 77 ms (PR) x 84 ms (v5).
7. Revisão: ver comentário na #2879. PHPUnit completo: 1391 testes OK.

Ressalvas
A. A flag de sessão exigir_2fa_cadastro só é ligada, nunca desligada: se o usuário volta a um grupo sem 2FA, fica preso na tela de ativação até novo login (falha fechada, baixo impacto).
B. GET em /mapos/alterarSenha (sem POST) executa confirmarSenha com senha vazia e conta uma falha; links/imagens de terceiros poderiam gastar as 5 tentativas da vítima logada (efeito: bloqueio de confirmação por ~1 min, escalando). Antes do PR o GET era inofensivo.
