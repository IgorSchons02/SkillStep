describe("Gestão de Trilhas - SkillStep", () => {
    beforeEach(() => {
        // Autenticação e navegação
        cy.visit("/login");
        cy.loginSkillStep("admin@cigam.com.br", "123");

        // Rota de trilhas
        cy.visit("/admin/trilhas");
    });

    it("Deve criar uma nova trilha e adicionar treinamentos sequenciais", () => {
        cy.contains("button", "Nova Trilha").click();

        // Aguarda a modal de configuração abrir
        cy.get("#modalTrilha")
            .should("be.visible")
            .should("have.class", "show");

        // Preenche dados básicos com invoke (padrão de estabilidade Igor)
        cy.get("#nomeTrilha")
            .invoke("val", "Formação Fullstack C#")
            .trigger("input")
            .trigger("change");

        cy.get("#descricaoTrilha")
            .invoke(
                "val",
                "Trilha completa de C# com ExtJS e Clean Architecture.",
            )
            .trigger("input")
            .trigger("change");

        // Interação com o Dual List Box de Trilhas
        // Adiciona os dois primeiros treinamentos disponíveis
        cy.get("#listaDisponiveis button").first().click();
        cy.get("#listaDisponiveis button").first().click();

        // Valida se os itens foram para a sequência da trilha
        cy.get("#listaSelecionadas .list-group-item").should("have.length", 2);

        // Valida contadores e tempo total (via JS do Blade)
        cy.get("#countSelecionadas").should("not.have.text", "0");
        cy.get("#tempoTotalModal").should("not.contain", "0h");

        // Salvar Trilha
        cy.contains("button", "Salvar Trilha").click();

        // Verifica se o card foi gerado no grid
        cy.get(".card-trilha").should("contain", "Formação Fullstack C#");
    });

    it("Deve visualizar os detalhes da trilha e verificar carga horária", () => {
        // Clica no botão visualizar do primeiro card
        cy.get(".card-trilha").first().contains("Visualizar").click();

        cy.get("#modalVisualizarTrilha")
            .should("be.visible")
            .should("have.class", "show");

        // Valida preenchimento dinâmico na visualização
        cy.get("#viewNomeTrilha").should("not.be.empty");
        cy.get("#viewTotalTreinamentos").should("not.have.text", "0");
        cy.get("#viewTempoTotalTrilha").should("not.contain", "0h");
        cy.get("#viewListaTreinamentos")
            .find(".list-group-item")
            .should("exist");

        cy.get("#modalVisualizarTrilha").find(".btn-close").click();
    });

    it("Deve editar uma trilha e testar o switch de status", () => {
        // Clica no botão editar (ícone lápis)
        cy.get(".card-trilha")
            .first()
            .find(".bi-pencil")
            .closest("button")
            .click();

        cy.get("#modalTrilha").should("be.visible");

        cy.get("#nomeTrilha")
            .invoke("val", "Trilha Editada Cypress")
            .trigger("input")
            .trigger("change");

        // Testa o switch de status
        cy.get("#statusTrilha").uncheck({ force: true });
        cy.get("#labelAtivo").should("have.text", "Trilha Inativa");

        cy.contains("button", "Salvar Trilha").click();

        // Valida alteração no grid
        cy.get(".card-trilha")
            .first()
            .should("contain", "Trilha Editada Cypress");
        cy.get(".card-trilha")
            .first()
            .find(".badge")
            .should("contain", "Inativa");
    });

    it("Deve impedir salvar trilha sem treinamentos", () => {
        cy.contains("button", "Nova Trilha").click();

        cy.get("#nomeTrilha").invoke("val", "Trilha Inválida").trigger("input");

        cy.contains("button", "Salvar Trilha").click();

        // Valida div de erro via JS
        cy.get("#erroListaVazia").should("not.have.class", "d-none");
    });

    it("Deve testar os filtros de pesquisa", () => {
        cy.get("#searchInput")
            .invoke("val", "Formação")
            .trigger("input")
            .trigger("keyup");

        cy.wait(600); // Aguarda o timeout de 500ms do seu script

        cy.get("#filtroStatus").select("1"); // Apenas Ativas
        cy.wait(600);

        cy.get(".row-cols-1").should("exist");
    });
});
