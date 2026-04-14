describe("Gestão de Planos de Estudo - SkillStep", () => {
    beforeEach(() => {
        // Autenticação e navegação
        cy.visit("/login");
        cy.loginSkillStep("admin@cigam.com.br", "123");

        // Rota base de planos de estudo
        cy.visit("/admin/planos");
    });

    it("Deve criar um novo plano de estudos e adicionar uma trilha estrutural", () => {
        cy.contains("button", "Novo Plano").click();

        // Aguarda a modal principal abrir
        cy.get("#modalPlano").should("be.visible").should("have.class", "show");

        // Preenche o título do plano
        cy.get("#tituloPlano")
            .invoke("val", "Nivelamento Backend C# (E2E)")
            .trigger("input")
            .trigger("change");

        // --- Select2: Aluno ---
        cy.get("#alunoPlano").next(".select2-container").click();
        cy.get(".select2-results__option")
            .not(':contains("Pesquise")')
            .first()
            .click();

        // --- Select2: Supervisores (Múltiplo) ---
        cy.get("#supervisoresPlano").next(".select2-container").click();
        cy.get(".select2-results__option").first().click();
        cy.get("body").click(0, 0); // Fecha o dropdown

        // Interação para adicionar uma Trilha
        cy.contains("button", "Adicionar Trilha").click();

        // Garante que a modal de busca está visível e carregada
        cy.get("#modalBusca").should("be.visible").should("have.class", "show");

        // --- CORREÇÃO BULLETPROOF PARA DATA ---
        // Força o preenchimento no DOM para garantir estabilidade no campo type="date"
        cy.get("#dataSugeridaTrilha")
            .invoke("val", "2026-12-31")
            .trigger("input", { force: true })
            .trigger("change", { force: true });

        // Trava o teste até que o valor seja confirmado no input
        cy.get("#dataSugeridaTrilha").should("have.value", "2026-12-31");

        // Agora clica no item da lista com segurança
        cy.get("#listaBuscaItens button").first().click({ force: true });

        // Validações na Tree View (Estrutura)
        cy.get("#treeViewContainer .tree-node").should(
            "have.length.at.least",
            1,
        );
        cy.get("#tempoTotalGeral").should("not.contain", "0h");

        // Salva o plano completo
        cy.get("#formPlano").submit();

        // Verifica se o card foi gerado corretamente no grid
        cy.get(".card-plano").should("contain", "Nivelamento Backend C# (E2E)");
    });

    it("Deve visualizar os detalhes do plano e seu progresso", () => {
        cy.get(".card-plano").first().find(".bi-eye").closest("button").click();

        cy.get("#modalVisualizar")
            .should("be.visible")
            .should("have.class", "show");

        cy.get("#visTitulo").should("not.be.empty");
        cy.get("#visCargaHoraria").should("not.contain", "0h");
        cy.get("#treeViewContainerVis .tree-node").should("exist");

        cy.get("#modalVisualizar").find(".btn-close").click();
    });

    it("Deve editar um plano de estudos existente", () => {
        cy.get(".card-plano").first().contains("Editar Plano").click();

        cy.get("#modalPlano").should("be.visible").should("have.class", "show");

        // 1. Usa clear() + type() para simular o usuário digitando de verdade
        cy.get("#tituloPlano")
            .clear()
            .type("Plano Atualizado Via Teste", { delay: 30 });

        // 2. Clica no botão em vez de submeter o formulário (respeita o fluxo do DOM e do JS)
        cy.contains("button", "Salvar Plano").click();

        // 3. Procura no grid INTEIRO pelo card que contém o novo título,
        // evitando erros caso o backend mude a ordem de exibição após o save.
        cy.contains(".card-plano", "Plano Atualizado Via Teste").should(
            "be.visible",
        );
    });

    it("Deve impedir de salvar um plano sem estrutura", () => {
        cy.contains("button", "Novo Plano").click();

        cy.get("#tituloPlano").invoke("val", "Plano Vazio").trigger("input");

        // Seleciona um aluno para passar na validação de campos obrigatórios
        cy.get("#alunoPlano").next(".select2-container").click();
        cy.get(".select2-results__option")
            .not(':contains("Pesquise")')
            .first()
            .click();

        cy.get("#formPlano").submit();

        // Valida o alerta do SweetAlert2 informando que o plano está vazio
        cy.get(".swal2-modal").should("be.visible");
        cy.get(".swal2-title").should("contain", "Plano Vazio");
        cy.get(".swal2-confirm").click();
    });

    it("Deve testar a pesquisa e o filtro de status no grid", () => {
        cy.get("#pesquisaPlanoGrid")
            .invoke("val", "Backend")
            .trigger("input")
            .trigger("keyup");

        cy.wait(600); // Respeita o debounce de 500ms do script

        cy.get("#filtroStatus").select("andamento");
        cy.wait(600);

        cy.get("#gridPlanos").should("exist");
    });
});
