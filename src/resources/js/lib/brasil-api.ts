/**
 * Service para integração com APIs de localidades do Brasil
 * Usa primeiro a API oficial do IBGE, com fallback para BrasilAPI
 * 
 * Documentação:
 * - IBGE: https://servicodados.ibge.gov.br/api/docs/localidades
 * - BrasilAPI: https://brasilapi.com.br/docs
 */

export interface Estado {
    id: number;
    sigla: string;
    nome: string;
    regiao: {
        id: number;
        sigla: string;
        nome: string;
    };
}

export interface Cidade {
    codigo_ibge: string;
    nome: string;
}

export interface CepData {
    cep: string;
    state: string;
    city: string;
    neighborhood: string;
    street: string;
    service: string;
}

// Interfaces para a API do IBGE
interface IBGEEstado {
    id: number;
    sigla: string;
    nome: string;
    regiao: {
        id: number;
        sigla: string;
        nome: string;
    };
}

interface IBGEMunicipio {
    id: number;
    nome: string;
}

/**
 * Normaliza texto para formato consistente
 * - Remove espaços extras
 * - Aplica Title Case (primeira letra maiúscula, resto minúscula)
 * - Mantém acentos e caracteres especiais
 * - Trata preposições (de, da, do, dos, das) em minúsculas, exceto no início
 * @param text - Texto a ser normalizado
 * @returns Texto normalizado
 */
function normalizeText(text: string): string {
    if (!text) {
        return '';
    }
    
    // Preposições que devem ficar em minúsculas (exceto no início)
    const preposicoes = ['de', 'da', 'do', 'dos', 'das', 'e'];
    
    return text
        .trim() // Remove espaços no início e fim
        .replace(/\s+/g, ' ') // Substitui múltiplos espaços por um único
        .split(' ')
        .map((word, index) => {
            if (word.length === 0) {
                return word;
            }
            
            const wordLower = word.toLowerCase();
            
            // Se for preposição e não for a primeira palavra, mantém minúscula
            if (index > 0 && preposicoes.includes(wordLower)) {
                return wordLower;
            }
            
            // Aplica Title Case: primeira letra maiúscula, resto minúscula
            return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
        })
        .join(' ');
}

/**
 * Busca todos os estados do Brasil
 * Tenta primeiro a API do IBGE, depois BrasilAPI como fallback
 * @returns Promise com array de estados
 */
export async function fetchEstados(): Promise<Estado[]> {
    // Tentar primeiro a API do IBGE
    try {
        const response = await fetch('https://servicodados.ibge.gov.br/api/v1/localidades/estados?orderBy=nome');
        
        if (!response.ok) {
            throw new Error('Erro ao buscar estados no IBGE');
        }
        
        const ibgeEstados: IBGEEstado[] = await response.json();
        
        // Mapear para o formato esperado e normalizar nomes
        return ibgeEstados.map(estado => ({
            id: estado.id,
            sigla: estado.sigla.toUpperCase(),
            nome: normalizeText(estado.nome),
            regiao: {
                id: estado.regiao.id,
                sigla: estado.regiao.sigla.toUpperCase(),
                nome: normalizeText(estado.regiao.nome),
            },
        }));
    } catch (error) {
        console.warn('Erro ao buscar estados no IBGE, tentando BrasilAPI:', error);
        
        // Fallback para BrasilAPI
        try {
            const response = await fetch('https://brasilapi.com.br/api/ibge/uf/v1');
            
            if (!response.ok) {
                throw new Error('Erro ao buscar estados na BrasilAPI');
            }
            
            const estados = await response.json();
            
            // Normalizar estados da BrasilAPI
            return estados.map((estado: Estado) => ({
                ...estado,
                sigla: estado.sigla.toUpperCase(),
                nome: normalizeText(estado.nome),
                regiao: {
                    ...estado.regiao,
                    sigla: estado.regiao.sigla.toUpperCase(),
                    nome: normalizeText(estado.regiao.nome),
                },
            }));
        } catch (fallbackError) {
            console.error('Erro ao buscar estados:', fallbackError);
            throw fallbackError;
        }
    }
}

/**
 * Busca todas as cidades de um estado específico
 * Tenta primeiro a API do IBGE, depois BrasilAPI como fallback
 * @param siglaUF - Sigla do estado (ex: "SP", "RJ")
 * @returns Promise com array de cidades
 */
export async function fetchCidades(siglaUF: string): Promise<Cidade[]> {
    if (!siglaUF) {
        return [];
    }
    
    // Tentar primeiro a API do IBGE
    try {
        const response = await fetch(
            `https://servicodados.ibge.gov.br/api/v1/localidades/estados/${siglaUF}/municipios?orderBy=nome`
        );
        
        if (!response.ok) {
            throw new Error('Erro ao buscar cidades no IBGE');
        }
        
        const ibgeMunicipios: IBGEMunicipio[] = await response.json();
        
        // Mapear para o formato esperado e normalizar nomes
        return ibgeMunicipios.map(municipio => ({
            codigo_ibge: municipio.id.toString(),
            nome: normalizeText(municipio.nome),
        }));
    } catch (error) {
        console.warn('Erro ao buscar cidades no IBGE, tentando BrasilAPI:', error);
        
        // Fallback para BrasilAPI
        try {
            const response = await fetch(
                `https://brasilapi.com.br/api/ibge/municipios/v1/${siglaUF}?providers=dados-abertos-br,gov,wikipedia`
            );
            
            if (!response.ok) {
                throw new Error('Erro ao buscar cidades na BrasilAPI');
            }
            
            const cidades = await response.json();
            
            // Normalizar cidades da BrasilAPI
            return cidades.map((cidade: Cidade) => ({
                ...cidade,
                nome: normalizeText(cidade.nome),
            }));
        } catch (fallbackError) {
            console.error('Erro ao buscar cidades:', fallbackError);
            throw fallbackError;
        }
    }
}

/**
 * Busca informações de endereço pelo CEP
 * @param cep - CEP sem formatação (apenas números)
 * @returns Promise com dados do endereço
 */
export async function fetchCep(cep: string): Promise<CepData> {
    try {
        const cepLimpo = cep.replace(/\D/g, '');
        
        if (cepLimpo.length !== 8) {
            throw new Error('CEP deve conter 8 dígitos');
        }
        
        const response = await fetch(`https://brasilapi.com.br/api/cep/v2/${cepLimpo}`);
        
        if (!response.ok) {
            throw new Error('CEP não encontrado');
        }
        
        const data = await response.json();
        
        // Normalizar dados do CEP
        return {
            ...data,
            state: data.state ? data.state.toUpperCase() : data.state,
            city: data.city ? normalizeText(data.city) : data.city,
            neighborhood: data.neighborhood ? normalizeText(data.neighborhood) : data.neighborhood,
            street: data.street ? normalizeText(data.street) : data.street,
        };
    } catch (error) {
        console.error('Erro ao buscar CEP:', error);
        throw error;
    }
}

