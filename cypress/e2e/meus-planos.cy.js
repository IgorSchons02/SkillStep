describe("Interação do Aluno - Meus Planos de Estudo (SkillStep)", () => {
    beforeEach(() => {
        // Autenticação (Ajuste o e-mail para um usuário com perfil de aluno)
        cy.visit("/login");
        cy.loginSkillStep("igor@cigam.com.br", "123");

        // Rota para a listagem de planos do aluno
        cy.visit("/meus-planos");
    });

    it("Deve abrir um plano e marcar uma tarefa como concluída", () => {
        // Entra no primeiro plano disponível no grid
        cy.get(".card-meu-plano").first().find("a").click();

        // Garante que a página de detalhes carregou a árvore
        cy.get("#jornadaContainer").should("be.visible");

        // Intercepta a chamada PUT que salva o progresso silenciosamente
        cy.intercept("PUT", "/meus-planos/*/progresso").as("salvarProgresso");

        // Captura o tempo concluído atual e a porcentagem para comparar se o cálculo JS funcionou
        cy.get("#percentGeral")
            .invoke("text")
            .then((percentInicial) => {
                cy.get("#tempoConcluido")
                    .invoke("text")
                    .then((tempoInicial) => {
                        // Busca a primeira tarefa NÃO concluída e clica no checkbox (.check-box)
                        cy.get(".task-item")
                            .not(".done")
                            .first()
                            .within(() => {
                                cy.get(".check-box").click();
                            });

                        // Aguarda o fetch para o backend e valida se retornou HTTP 200 (Sucesso)
                        cy.wait("@salvarProgresso")
                            .its("response.statusCode")
                            .should("eq", 200);

                        // Validações Visuais da Tarefa
                        cy.get(".task-item")
                            .first()
                            .should("have.class", "done");
                        cy.get(".task-item")
                            .first()
                            .find(".bi-check-lg")
                            .should("exist");

                        // Valida se a porcentagem geral e o tempo concluído foram alterados no painel lateral
                        cy.get("#percentGeral")
                            .invoke("text")
                            .should("not.eq", percentInicial);
                        cy.get("#tempoConcluido")
                            .invoke("text")
                            .should("not.eq", tempoInicial);
                    });
            });
    });

    it("Deve desmarcar uma tarefa previamente concluída", () => {
        cy.get(".card-meu-plano").first().find("a").click();
        cy.get("#jornadaContainer").should("be.visible");

        cy.intercept("PUT", "/meus-planos/*/progresso").as("reverterProgresso");

        cy.get("#percentGeral")
            .invoke("text")
            .then((percentInicial) => {
                cy.get("#tempoConcluido")
                    .invoke("text")
                    .then((tempoInicial) => {
                        // Busca a primeira tarefa CONCLUÍDA (.done) e clica no checkbox para reverter
                        cy.get(".task-item.done")
                            .first()
                            .within(() => {
                                cy.get(".check-box").click();
                            });

                        // Aguarda o fetch para o backend atualizar a estrutura
                        cy.wait("@reverterProgresso")
                            .its("response.statusCode")
                            .should("eq", 200);

                        // Validações Visuais de Reversão
                        cy.get(".task-item")
                            .first()
                            .should("not.have.class", "done");
                        cy.get(".task-item")
                            .first()
                            .find(".bi-check-lg")
                            .should("not.exist");

                        // Valida se o painel lateral regrediu os valores de progresso
                        cy.get("#percentGeral")
                            .invoke("text")
                            .should("not.eq", percentInicial);
                        cy.get("#tempoConcluido")
                            .invoke("text")
                            .should("not.eq", tempoInicial);
                    });
            });
    });

    it("Deve visualizar os detalhes/links e instruções de uma tarefa", () => {
        cy.get(".card-meu-plano").first().find("a").click();
        cy.get("#jornadaContainer").should("be.visible");

        // Clica no ícone "i" de informação da primeira tarefa
        cy.get(".icon-info-task").first().click();

        // Valida se a div '.task-instructions' logo abaixo dela abriu e ficou visível
        cy.get(".task-wrapper")
            .first()
            .find(".task-instructions")
            .should("have.class", "active")
            .and("be.visible");

        // Valida se há conteúdo dentro da instrução
        cy.get(".task-wrapper")
            .first()
            .find(".task-instructions p")
            .should("not.be.empty");
    });

    it("Deve fechar e abrir as sessões (Trilhas e Treinamentos)", () => {
        cy.get(".card-meu-plano").first().find("a").click();

        // Pega o header do primeiro treinamento e clica para contraí-lo
        cy.get(".treino-header").first().click();

        // Valida se a div de conteúdo perdeu a classe active
        cy.get(".treino-header")
            .first()
            .next(".tree-content")
            .should("not.have.class", "active");

        // Valida se o ícone do chevron virou para a direita
        cy.get(".treino-header")
            .first()
            .find(".bi-chevron-right")
            .should("exist");

        // Clica novamente para expandir
        cy.get(".treino-header").first().click();
        cy.get(".treino-header")
            .first()
            .next(".tree-content")
            .should("have.class", "active");
        cy.get(".treino-header")
            .first()
            .find(".bi-chevron-down")
            .should("exist");
    });
});
