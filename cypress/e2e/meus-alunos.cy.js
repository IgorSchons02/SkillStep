describe("Acompanhamento de Alunos (Supervisor) - SkillStep", () => {
    beforeEach(() => {
        // Autenticação (Ajuste o e-mail para um usuário com perfil de supervisor/admin)
        cy.visit("/login");
        cy.loginSkillStep("cristiano@cigam.com.br", "123");

        // Rota base de acompanhamento de alunos
        cy.visit("/meus-alunos");
    });

    it("Deve carregar a listagem de planos dos alunos", () => {
        // Garante que o grid carregou os cards gerados pelo JavaScript
        cy.get(".card-plano").should("exist");

        // Verifica se os cards possuem as informações básicas renderizadas
        cy.get(".card-plano")
            .first()
            .within(() => {
                cy.get("h5").should("not.be.empty"); // Título do plano
                cy.get("p").contains("Aluno:").should("exist");
                cy.get(".progress-bar").should("exist");
                cy.contains("button", "Ver Progresso").should("be.visible");
            });
    });

    it("Deve pesquisar por um aluno ou plano específico", () => {
        // Usa .type() para simular digitação real, acionando o evento 'keyup' do seu JS
        cy.get("#pesquisaPlanoGrid").type("Teste", { delay: 50 });

        // Aguarda o tempo do debounce (800ms) + tempo extra para renderização/submit
        cy.wait(1000);

        // Valida se o grid exibe resultados filtrados ou a mensagem de vazio
        cy.get("body").then(($body) => {
            if ($body.find(".card-plano").length > 0) {
                // Se achou um card, ele precisa estar visível
                cy.get(".card-plano").first().should("be.visible");
            } else {
                // Se não achou, a mensagem de 'Nenhum aluno' deve aparecer
                cy.contains(
                    "Nenhum aluno ou plano corresponde à sua busca",
                ).should("be.visible");
            }
        });
    });

    it("Deve filtrar planos por status (Concluídos)", () => {
        // Seleciona a opção 'concluido' no select
        cy.get("#filtroStatus").select("concluido");

        // O select possui um event listener de 'change' que dispara o form.submit()
        cy.wait(600);

        // Verifica o resultado do filtro
        cy.get("body").then(($body) => {
            if ($body.find(".card-plano").length > 0) {
                // Verifica se todos os cards exibidos possuem o layout de "Concluído"
                cy.get(".card-plano").each(($el) => {
                    cy.wrap($el)
                        .find(".badge")
                        .should("contain.text", "Concluído");
                    cy.wrap($el).should("have.class", "border-success");
                });
            } else {
                cy.contains(
                    "Nenhum aluno ou plano corresponde à sua busca",
                ).should("be.visible");
            }
        });
    });

    it("Deve abrir a modal e visualizar a árvore de progresso do aluno", () => {
        // Clica no botão "Ver Progresso" do primeiro card
        cy.get(".card-plano")
            .first()
            .contains("button", "Ver Progresso")
            .click();

        // Aguarda a animação da modal do Bootstrap
        cy.get("#modalVisualizar")
            .should("be.visible")
            .should("have.class", "show");

        // Verifica se os dados do cabeçalho da modal foram preenchidos corretamente
        cy.get("#visTitulo").should("not.be.empty");
        cy.get("#visAluno").should("not.be.empty");
        cy.get("#visPercent").should("not.contain", "NaN"); // Garante que o JS calculou corretamente

        // Verifica se a árvore (Tree View) estrutural não está vazia
        cy.get("#treeViewContainerVis").should("not.be.empty");
        cy.get(".tree-node").should("exist");

        // Testa a interação de expandir/contrair a árvore (toggleTree)
        // Pega o header da primeira trilha e clica para fechar
        cy.get(".tree-header").first().click();

        // Verifica se a seta apontou para a direita (indicando que contraiu)
        cy.get(".tree-header")
            .first()
            .find("i.bi-chevron-right")
            .should("exist");

        // Clica para abrir novamente
        cy.get(".tree-header").first().click();
        cy.get(".tree-header")
            .first()
            .find("i.bi-chevron-down")
            .should("exist");

        // Fecha a modal
        cy.get("#modalVisualizar").find(".btn-close").click();
        cy.get("#modalVisualizar").should("not.have.class", "show");
    });
});
