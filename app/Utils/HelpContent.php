<?php

namespace App\Utils;

class HelpContent
{
    public static function get(string $page, string $role): array
    {
        $content = self::getContent();
        
        if (!isset($content[$page]) || !isset($content[$page][$role])) {
            if ($role === 'coordenador' && isset($content[$page]['membro'])) {
                return $content[$page]['membro'];
            }
            return self::getDefaultHelp($role);
        }
        
        return $content[$page][$role];
    }
    
    private static function getDefaultHelp(string $role): array
    {
        return [
            'title' => 'Ajuda',
            'content' => '<p>Consulte o guia completo do utilizador para mais informações.</p>',
            'guide_link' => '/guides/indice'
        ];
    }
    
    private static function getContent(): array
    {
        return [
            'dashboard' => [
                'vigilante' => [
                    'title' => 'Dashboard',
                    'content' => '<h4>📊 Visão Geral:</h4><ul><li><strong>Vagas Abertas:</strong> Número de vagas disponíveis</li><li><strong>Próximos Júris:</strong> Júris em que está alocado</li><li><strong>Status:</strong> Seu estado de disponibilidade</li></ul>',
                    'guide_link' => '/guides/parte1'
                ],
                'membro' => [
                    'title' => 'Dashboard',
                    'content' => '<h4>📊 Estatísticas:</h4><ul><li><strong>Vagas Abertas:</strong> Aceitando candidaturas</li><li><strong>Vigilantes Disponíveis:</strong> Prontos para alocação</li><li><strong>Próximos Júris:</strong> Do próximo dia</li></ul>',
                    'guide_link' => '/guides/parte2'
                ]
            ],
            
            'availability' => [
                'vigilante' => [
                    'title' => 'Candidaturas',
                    'content' => '<h4>✅ Passo a Passo:</h4><ol><li><strong>Complete perfil</strong> (telefone, NUIT, NIB, banco)</li><li><strong>Veja vagas</strong> abertas</li><li><strong>Clique "Candidatar-me"</strong></li><li><strong>Aguarde aprovação</strong></li></ol><h4>📋 Status:</h4><ul><li>🟡 Pendente | 🟢 Aprovada | 🔴 Rejeitada</li></ul>',
                    'guide_link' => '/guides/parte1'
                ]
            ],
            
            'vacancies' => [
                'membro' => [
                    'title' => 'Gestão de Vagas',
                    'content' => '<h4>➕ Criar Vaga:</h4><p>"+ Nova Vaga" → Preencha título, descrição, prazo</p><h4>🚦 Estados:</h4><ul><li>🟢 Aberta: Aceitando</li><li>🔴 Fechada: Parou candidaturas</li><li>⚫ Encerrada: Arquivada</li></ul><h4>💡 Dica:</h4><p>Prefira Fechar/Encerrar ao invés de Eliminar</p>',
                    'guide_link' => '/guides/parte2'
                ]
            ],
            
            'applications' => [
                'membro' => [
                    'title' => 'Revisar Candidaturas',
                    'content' => '<h4>✅ Aprovar:</h4><p>Verifique perfil completo → "✅ Aprovar"</p><h4>❌ Rejeitar:</h4><p>"❌ Rejeitar" → <strong>Motivo obrigatório</strong></p><h4>💡 Dica:</h4><p>Revise em até 48h para melhor experiência</p>',
                    'guide_link' => '/guides/parte2'
                ]
            ],
            
            'juries' => [
                'vigilante' => [
                    'title' => 'Meus Júris',
                    'content' => '<h4>👁️ Visualize:</h4><ul><li>Disciplina, Data, Horário</li><li>Local e Sala</li><li>Supervisor e outros vigilantes</li></ul><p><strong>Nota:</strong> Não pode editar. Contate comissão para alterações.</p>',
                    'guide_link' => '/guides/parte1'
                ],
                'membro' => [
                    'title' => 'Gestão de Júris',
                    'content' => '<h4>📋 3 Interfaces:</h4><ol><li><strong>Lista:</strong> Visão completa</li><li><strong>Por Vaga:</strong> Focado</li><li><strong>Avançado:</strong> Drag-drop ⭐</li></ol><h4>➕ Criar:</h4><ul><li>Individual: "+ Novo"</li><li>Massa: Importar Excel</li><li>Template: Usar Template</li></ul>',
                    'guide_link' => '/guides/parte2'
                ]
            ],
            
            'juries-planning' => [
                'membro' => [
                    'title' => 'Planeamento Avançado',
                    'content' => '<h4>🎯 Alocar:</h4><ol><li><strong>Arraste</strong> vigilante para júri</li><li>Feedback: 🟢 OK | 🟡 Aviso | 🔴 Bloqueado</li><li><strong>Solte</strong> → Alocado!</li></ol><h4>⚡ Auto-Alocação:</h4><p>"Auto-Alocar Completo" → Preenche TODOS os júris automaticamente. <strong>Economiza 80% do tempo!</strong></p>',
                    'guide_link' => '/guides/parte2'
                ]
            ],
            
            'locations-templates' => [
                'membro' => [
                    'title' => 'Templates de Locais',
                    'content' => '<h4>💾 O que são?</h4><p>Configurações salvas para reutilização</p><h4>➕ Criar:</h4><p>Nome → Local → Disciplinas + Salas</p><h4>🔄 Usar:</h4><p>"Usar Template" → Vaga + Data → Cria todos os júris!</p><h4>⚡ Economia:</h4><p>50 júris: 5 min vs 2h manual</p>',
                    'guide_link' => '/guides/parte2'
                ]
            ],
            
            'locations-import' => [
                'membro' => [
                    'title' => 'Importar Excel',
                    'content' => '<h4>📊 Como:</h4><ol><li>Baixe template Excel</li><li>Preencha: Local, Data, Disciplina, Sala, Horário</li><li>Upload → Sistema valida e cria</li></ol><h4>💡 Formatos:</h4><p>XLSX, XLS, CSV</p>',
                    'guide_link' => '/guides/parte2'
                ]
            ],
            
            'master-data-disciplines' => [
                'coordenador' => [
                    'title' => 'Disciplinas',
                    'content' => '<h4>➕ Criar:</h4><p>Código (MAT101) + Nome + Descrição</p><h4>🔛 Ativar/Desativar:</h4><p>Ativas aparecem em formulários</p><h4>💡 Dica:</h4><p>Prefira desativar ao invés de eliminar</p>',
                    'guide_link' => '/guides/parte3'
                ]
            ],
            
            'master-data-locations' => [
                'coordenador' => [
                    'title' => 'Locais',
                    'content' => '<h4>➕ Criar:</h4><p>Código + Nome + Cidade + Endereço + Capacidade</p><h4>📍 Hierarquia:</h4><p>Local → Salas → Júris</p><h4>⚠️ Eliminar:</h4><p>Elimine salas primeiro, depois local</p>',
                    'guide_link' => '/guides/parte3'
                ]
            ],
            
            'master-data-rooms' => [
                'coordenador' => [
                    'title' => 'Salas',
                    'content' => '<h4>➕ Criar:</h4><p>Local + Código + Nome + Capacidade</p><h4>💡 Dica:</h4><p>Capacidade realista considerando distanciamento</p>',
                    'guide_link' => '/guides/parte3'
                ]
            ],
            
            'profile' => [
                'vigilante' => [
                    'title' => 'Meu Perfil',
                    'content' => '<h4>✏️ Editar:</h4><p>Complete: Telefone, NUIT, NIB, Banco</p><h4>🔐 Senha:</h4><p>Mínimo 8 caracteres</p><h4>📷 Foto:</h4><p>JPG/PNG, máx 2MB</p>',
                    'guide_link' => '/guides/parte1'
                ],
                'membro' => [
                    'title' => 'Meu Perfil',
                    'content' => '<h4>✏️ Editar informações pessoais</h4><h4>🔐 Alterar senha</h4><h4>📷 Upload foto</h4>',
                    'guide_link' => '/guides/parte2'
                ]
            ]
        ];
    }
}
