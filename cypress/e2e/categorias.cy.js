describe("Gestão de Categorias - SkillStep", () => {
    beforeEach(() => {
        // Login simplificado com seu comando customizado
        cy.visit("/login");
        cy.loginSkillStep("admin@cigam.com.br", "123");

        // Navega para a tela de categorias
        cy.visit("/admin/categorias");
    });

    it("Deve criar uma nova categoria com cor personalizada", () => {
        cy.contains("button", "Nova Categoria").click();

        // 1. Espera a modal estar totalmente carregada e visível
        cy.get("#modalNovaCategoria")
            .should("be.visible")
            .should("have.class", "show");

        // 2. Isolamos as ações dentro da modal para não perder o foco
        cy.get("#modalNovaCategoria").within(() => {
            // Injeta o texto do nome diretamente para evitar interrupção de scripts
            cy.get('input[name="nome"]')
                .invoke("val", "Cybersecurity")
                .trigger("input") // Avisa o navegador da mudança
                .trigger("change"); // Confirma

            // A descrição geralmente não tem validações agressivas, mas podemos usar delay por segurança
            cy.get('textarea[name="descricao"]')
                .clear()
                .type("Treinamentos focados em segurança da informação.", {
                    delay: 50,
                });

            // Interagindo com o input de cor
            cy.get('input[name="cor_hex"]')
                .invoke("val", "#ff0000")
                .trigger("change");

            cy.get('button[type="submit"]').click();
        });

        // Validação: verifica se o card apareceu na listagem
        cy.get(".category-card").should("contain", "Cybersecurity");
    });

    it("Deve editar uma categoria existente", () => {
        // 1. Abre o dropdown do primeiro card
        cy.get(".category-card")
            .first()
            .find('button[data-bs-toggle="dropdown"]')
            .click();

        // 2. Clica no botão Editar
        cy.contains("button", "Editar").click();

        // 3. Aguarda a modal de edição
        cy.get("#modalEditarCategoria")
            .should("be.visible")
            .should("have.class", "show");

        // Usando o invoke também na edição para garantir estabilidade
        cy.get("#edit_cat_nome")
            .invoke("val", "Infraestrutura")
            .trigger("input")
            .trigger("change");

        cy.get('#modalEditarCategoria button[type="submit"]').click();

        // 4. Verifica se a alteração refletiu no card
        cy.get(".category-card").should("contain", "Infraestrutura");
    });

    it("Deve testar a busca", () => {
        // Para a busca, injetamos o valor e disparamos explicitamente o evento 'keyup'
        cy.get("#searchInput")
            .invoke("val", "Segurança")
            .trigger("input")
            .trigger("keyup");

        // Aguarda o tempo do debounce (500ms) + tempo de resposta do servidor
        cy.wait(1200);

        // Verifica os resultados
        cy.get(".row.g-4").then(($el) => {
            if ($el.find(".category-card").length > 0) {
                cy.get(".category-card").should("contain", "Infraestrutura");
            } else {
                cy.contains("Nenhuma categoria encontrada").should(
                    "be.visible",
                );
            }
        });
    });

    it("Deve cancelar a exclusão de uma categoria no Alert", () => {
        cy.get(".category-card")
            .first()
            .find('button[data-bs-toggle="dropdown"]')
            .click();
        cy.get(".btn-delete-category").first().click();

        // Valida se o SweetAlert apareceu
        cy.get(".swal2-modal").should("be.visible");
        cy.get(".swal2-title").should("contain", "Excluir Categoria?");

        // Clica no botão de cancelar do SweetAlert
        cy.contains("button", "Cancelar").click();

        // Garante que o card ainda existe e o alerta sumiu
        cy.get(".swal2-modal").should("not.exist");
        cy.get(".category-card").should("exist");
    });
});
