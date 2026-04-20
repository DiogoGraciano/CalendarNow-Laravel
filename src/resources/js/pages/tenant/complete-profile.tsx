import UpdateTenantAction from '@/actions/App/Actions/Tenant/UpdateTenantAction';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Transition } from '@headlessui/react';
import { useTranslation } from 'react-i18next';
import { useState, useRef, useEffect } from 'react';
import {
    fetchEstados,
    fetchCidades,
    fetchCep,
    type Estado,
    type Cidade,
} from '@/lib/brasil-api';
import { validateWithYup } from '@/hooks/use-yup-validation';
import { maskCep, maskPhone, createMaskHandler, removeMask } from '@/lib/masks';
import * as yup from 'yup';
import { useMemo } from 'react';

interface Segment {
    value: string;
    label: string;
}

interface Tenant {
    id: string;
    name: string;
    email?: string;
    phone?: string;
    website?: string;
    address?: string;
    city?: string;
    state?: string;
    zip?: string;
    country?: string;
    neighborhood?: string;
    segment?: string;
}

interface CompleteTenantProfileProps {
    tenant: Tenant;
    segments: Segment[];
}

export default function CompleteTenantProfile({
    tenant,
    segments,
}: CompleteTenantProfileProps) {
    const { t } = useTranslation('common');
    const [isSearchingCep, setIsSearchingCep] = useState(false);
    const [cepError, setCepError] = useState<string | null>(null);
    const [estados, setEstados] = useState<Estado[]>([]);
    const [cidades, setCidades] = useState<Cidade[]>([]);
    const [selectedEstado, setSelectedEstado] = useState<string>(tenant.state || '');
    const [selectedCidadeCodigo, setSelectedCidadeCodigo] = useState<string>('');
    const [isLoadingEstados, setIsLoadingEstados] = useState(true);
    const [isLoadingCidades, setIsLoadingCidades] = useState(false);

    // Função helper para encontrar o código IBGE da cidade pelo nome
    const findCidadeCodigoByName = (nome: string): string => {
        const cidade = cidades.find(c => c.nome === nome);
        return cidade?.codigo_ibge || '';
    };

    // Função helper para encontrar o nome da cidade pelo código IBGE
    const findCidadeNameByCodigo = (codigo: string): string => {
        const cidade = cidades.find(c => c.codigo_ibge === codigo);
        return cidade?.nome || '';
    };
    
    const addressRef = useRef<HTMLInputElement>(null);
    const neighborhoodRef = useRef<HTMLInputElement>(null);
    const citySelectRef = useRef<HTMLButtonElement>(null);
    const stateSelectRef = useRef<HTMLButtonElement>(null);
    const zipRef = useRef<HTMLInputElement>(null);

    // Carregar estados ao montar o componente
    useEffect(() => {
        const loadEstados = async () => {
            try {
                setIsLoadingEstados(true);
                const estadosData = await fetchEstados();
                setEstados(estadosData);
            } catch (error) {
                console.error('Erro ao carregar estados:', error);
            } finally {
                setIsLoadingEstados(false);
            }
        };

        loadEstados();
    }, []);

    // Carregar cidades quando um estado for selecionado
    useEffect(() => {
        const loadCidades = async () => {
            if (!selectedEstado) {
                setCidades([]);
                setSelectedCidadeCodigo('');
                return;
            }

            try {
                setIsLoadingCidades(true);
                const cidadesData = await fetchCidades(selectedEstado);
                setCidades(cidadesData);
                
                // Se já havia uma cidade selecionada (do tenant), tentar encontrar pelo nome
                if (tenant.city && !selectedCidadeCodigo) {
                    const codigo = cidadesData.find(c => c.nome === tenant.city)?.codigo_ibge;
                    if (codigo) {
                        setSelectedCidadeCodigo(codigo);
                    }
                } else if (selectedCidadeCodigo && !cidadesData.find(c => c.codigo_ibge === selectedCidadeCodigo)) {
                    // Se o código não existe mais na lista, limpar
                    setSelectedCidadeCodigo('');
                }
            } catch (error) {
                console.error('Erro ao carregar cidades:', error);
                setCidades([]);
            } finally {
                setIsLoadingCidades(false);
            }
        };

        loadCidades();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [selectedEstado]);

    const handleSearchCep = async (setFormData?: (key: string, value: any) => void) => {
        const cep = removeMask(zipRef.current?.value || '');
        
        if (cep.length !== 8) {
            setCepError('CEP deve conter 8 dígitos');
            return;
        }

        setIsSearchingCep(true);
        setCepError(null);

        try {
            const cepData = await fetchCep(cep);

            // Atualizar campos do formulário usando setData
            if (setFormData) {
                if (cepData.street) {
                    setFormData('address', cepData.street);
                }
                if (cepData.neighborhood) {
                    setFormData('neighborhood', cepData.neighborhood);
                }
            } else {
                // Fallback para atualizar refs se setData não estiver disponível
                if (addressRef.current) {
                    addressRef.current.value = cepData.street || '';
                }
                if (neighborhoodRef.current) {
                    neighborhoodRef.current.value = cepData.neighborhood || '';
                }
            }
            
            // Atualizar estado e cidade através dos selects
            if (cepData.state) {
                setSelectedEstado(cepData.state);
                
                // Aguardar as cidades carregarem antes de selecionar a cidade
                if (cepData.city) {
                    // Aguardar um pouco para garantir que as cidades foram carregadas
                    setTimeout(async () => {
                        try {
                            const cidadesData = await fetchCidades(cepData.state);
                            const cidadeEncontrada = cidadesData.find(
                                c => c.nome.toLowerCase() === cepData.city.toLowerCase()
                            );
                            if (cidadeEncontrada) {
                                setSelectedCidadeCodigo(cidadeEncontrada.codigo_ibge);
                            }
                        } catch (error) {
                            console.error('Erro ao buscar cidade:', error);
                        }
                    }, 300);
                }
            }
        } catch (error) {
            setCepError('CEP não encontrado. Verifique o CEP digitado.');
            console.error('Erro ao buscar CEP:', error);
        } finally {
            setIsSearchingCep(false);
        }
    };

    const handleEstadoChange = (value: string) => {
        setSelectedEstado(value);
        setSelectedCidadeCodigo(''); // Limpar cidade quando mudar o estado
    };

    const handleCepKeyPress = (e: React.KeyboardEvent<HTMLInputElement>, setFormData?: (key: string, value: any) => void) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleSearchCep(setFormData);
        }
    };

    return (
        <AppLayout>
            <Head title={t('tenant.completeProfile.pageTitle')} />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
                <HeadingSmall
                    title={t('tenant.completeProfile.title')}
                    description={t('tenant.completeProfile.description')}
                />

                <div className="rounded-lg border bg-card p-6">
                    <CompleteProfileForm
                        tenant={tenant}
                        segments={segments}
                        estados={estados}
                        cidades={cidades}
                        selectedEstado={selectedEstado}
                        setSelectedEstado={setSelectedEstado}
                        selectedCidadeCodigo={selectedCidadeCodigo}
                        setSelectedCidadeCodigo={setSelectedCidadeCodigo}
                        isLoadingCidades={isLoadingCidades}
                        isLoadingEstados={isLoadingEstados}
                        addressRef={addressRef}
                        neighborhoodRef={neighborhoodRef}
                        citySelectRef={citySelectRef}
                        stateSelectRef={stateSelectRef}
                        zipRef={zipRef}
                        handleSearchCep={handleSearchCep}
                        handleEstadoChange={handleEstadoChange}
                        handleCepKeyPress={handleCepKeyPress}
                        isSearchingCep={isSearchingCep}
                        cepError={cepError}
                        setCepError={setCepError}
                        findCidadeNameByCodigo={findCidadeNameByCodigo}
                    />
                </div>
            </div>
        </AppLayout>
    );
}

function CompleteProfileForm({
    tenant,
    segments,
    estados,
    cidades,
    selectedEstado,
    setSelectedEstado,
    selectedCidadeCodigo,
    setSelectedCidadeCodigo,
    isLoadingCidades,
    isLoadingEstados,
    addressRef,
    neighborhoodRef,
    citySelectRef,
    stateSelectRef,
    zipRef,
    handleSearchCep,
    handleEstadoChange,
    handleCepKeyPress,
    isSearchingCep,
    cepError,
    setCepError,
    findCidadeNameByCodigo,
}: {
    tenant: Tenant;
    segments: Segment[];
    estados: Estado[];
    cidades: Cidade[];
    selectedEstado: string;
    setSelectedEstado: (value: string) => void;
    selectedCidadeCodigo: string;
    setSelectedCidadeCodigo: (value: string) => void;
    isLoadingCidades: boolean;
    isLoadingEstados: boolean;
    addressRef: React.RefObject<HTMLInputElement | null>;
    neighborhoodRef: React.RefObject<HTMLInputElement | null>;
    citySelectRef: React.RefObject<HTMLButtonElement | null>;
    stateSelectRef: React.RefObject<HTMLButtonElement | null>;
    zipRef: React.RefObject<HTMLInputElement | null>;
    handleSearchCep: (setFormData?: (key: string, value: any) => void) => Promise<void>;
    handleEstadoChange: (value: string) => void;
    handleCepKeyPress: (e: React.KeyboardEvent<HTMLInputElement>, setFormData?: (key: string, value: any) => void) => void;
    isSearchingCep: boolean;
    cepError: string | null;
    setCepError: (error: string | null) => void;
    findCidadeNameByCodigo: (codigo: string) => string;
}) {
    const { t } = useTranslation();
    
    const { data, setData, put, processing, recentlySuccessful, errors, clearErrors, setError } = useForm({
        name: tenant.name || '',
        email: tenant.email || '',
        phone: tenant.phone ? maskPhone(tenant.phone) : '',
        website: tenant.website || '',
        address: tenant.address || '',
        city: tenant.city || '',
        state: tenant.state || '',
        zip: tenant.zip ? maskCep(tenant.zip) : '',
        country: tenant.country || '',
        neighborhood: tenant.neighborhood || '',
        segment: tenant.segment || '',
    });

    // Schema de validação Yup
    const completeProfileSchema = useMemo(() => {
        // Função helper para validar CEP
        const validateCEP = (cep: string): boolean => {
            const cleanCep = cep.replace(/\D/g, '');
            return cleanCep.length === 8;
        };

        return yup.object({
            name: yup
                .string()
                .required('O nome é obrigatório')
                .min(3, 'O nome deve ter pelo menos 3 caracteres')
                .max(255, 'O nome não pode ter mais de 255 caracteres'),
            email: yup
                .string()
                .email('Email inválido')
                .max(255, 'O email não pode ter mais de 255 caracteres')
                .nullable()
                .transform((value) => value || null),
            phone: yup
                .string()
                .max(255, 'O telefone não pode ter mais de 255 caracteres')
                .nullable()
                .transform((value) => value || null),
            website: yup
                .string()
                .url('URL inválida')
                .max(255, 'O website não pode ter mais de 255 caracteres')
                .nullable()
                .transform((value) => value || null),
            address: yup
                .string()
                .max(255, 'O endereço não pode ter mais de 255 caracteres')
                .nullable()
                .transform((value) => value || null),
            city: yup
                .string()
                .max(255, 'A cidade não pode ter mais de 255 caracteres')
                .nullable()
                .transform((value) => value || null),
            state: yup
                .string()
                .max(255, 'O estado não pode ter mais de 255 caracteres')
                .nullable()
                .transform((value) => value || null),
            zip: yup
                .string()
                .test('cep', 'CEP inválido', function(value) {
                    if (!value) return true;
                    return validateCEP(value);
                })
                .max(255, 'O CEP não pode ter mais de 255 caracteres')
                .nullable()
                .transform((value) => value || null),
            country: yup
                .string()
                .max(255, 'O país não pode ter mais de 255 caracteres')
                .nullable()
                .transform((value) => value || null),
            neighborhood: yup
                .string()
                .max(255, 'O bairro não pode ter mais de 255 caracteres')
                .nullable()
                .transform((value) => value || null),
            segment: yup
                .string()
                .nullable()
                .transform((value) => value || null),
        });
    }, []);

    // Aplicar máscaras nos valores iniciais se necessário
    useEffect(() => {
        if (tenant.phone && !data.phone.includes('(')) {
            setData('phone', maskPhone(tenant.phone));
        }
        if (tenant.zip && !data.zip.includes('-')) {
            setData('zip', maskCep(tenant.zip));
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const submit = async (e: React.FormEvent) => {
        e.preventDefault();
        
        // Validação client-side com Yup
        const yupErrors = await validateWithYup(completeProfileSchema, data);
        if (yupErrors) {
            Object.keys(yupErrors).forEach((key) => {
                setError(key as keyof typeof data, yupErrors[key]);
            });
            return;
        }
        
        clearErrors();
        
        put(UpdateTenantAction.url(), {
            preserveScroll: true,
        });
    };

    // Sincronizar estado selecionado com dados do formulário
    useEffect(() => {
        setData('state', selectedEstado);
    }, [selectedEstado, setData]);

    useEffect(() => {
        if (selectedCidadeCodigo) {
            const cidadeNome = findCidadeNameByCodigo(selectedCidadeCodigo);
            if (cidadeNome) {
                setData('city', cidadeNome);
            }
        }
    }, [selectedCidadeCodigo, findCidadeNameByCodigo, setData]);

    return (
        <form onSubmit={submit} className="space-y-6">
            <div className="grid gap-6 md:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="name">
                        {t('tenant.completeProfile.nameLabel')} *
                    </Label>
                    <Input
                        id="name"
                        name="name"
                        value={data.name}
                        onChange={(e) => {
                            setData('name', e.target.value);
                            if (errors.name) {
                                clearErrors('name');
                            }
                        }}
                        required
                        placeholder={t('tenant.completeProfile.namePlaceholder')}
                    />
                    <InputError message={errors.name} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="email">{t('tenant.completeProfile.emailLabel')}</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        onChange={(e) => {
                            setData('email', e.target.value);
                            if (errors.email) {
                                clearErrors('email');
                            }
                        }}
                        placeholder={t('tenant.completeProfile.emailPlaceholder')}
                    />
                    <InputError message={errors.email} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="phone">{t('tenant.completeProfile.phoneLabel')}</Label>
                    <Input
                        id="phone"
                        type="tel"
                        name="phone"
                        value={data.phone}
                        onChange={createMaskHandler(
                            maskPhone,
                            (value) => {
                                setData('phone', value);
                            },
                            () => {
                                if (errors.phone) {
                                    clearErrors('phone');
                                }
                            }
                        )}
                        placeholder={t('tenant.completeProfile.phonePlaceholder')}
                        maxLength={15}
                    />
                    <InputError message={errors.phone} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="website">{t('tenant.completeProfile.websiteLabel')}</Label>
                    <Input
                        id="website"
                        type="url"
                        name="website"
                        value={data.website}
                        onChange={(e) => {
                            setData('website', e.target.value);
                            if (errors.website) {
                                clearErrors('website');
                            }
                        }}
                        placeholder={t('tenant.completeProfile.websitePlaceholder')}
                    />
                    <InputError message={errors.website} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="segment_id">
                        {t('tenant.completeProfile.segmentLabel')}
                    </Label>
                    <Select
                        value={data.segment || undefined}
                        onValueChange={(value) => {
                            setData('segment', value || '');
                            if (errors.segment) {
                                clearErrors('segment');
                            }
                        }}
                    >
                        <SelectTrigger id="segment">
                            <SelectValue placeholder={t('tenant.completeProfile.segmentPlaceholder')} />
                        </SelectTrigger>
                        <SelectContent>
                            {segments.map((segment) => (
                                <SelectItem
                                    key={segment.value}
                                    value={segment.value}
                                >
                                    {segment.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <InputError message={errors.segment} />
                </div>
            </div>

            <div className="grid gap-2">
                <Label htmlFor="zip">{t('tenant.completeProfile.zipLabel')}</Label>
                <div className="flex gap-2">
                    <Input
                        ref={zipRef}
                        id="zip"
                        name="zip"
                        value={data.zip}
                        onChange={createMaskHandler(
                            maskCep,
                            (value) => {
                                setData('zip', value);
                            },
                            () => {
                                setCepError(null);
                                if (errors.zip) {
                                    clearErrors('zip');
                                }
                            }
                        )}
                        placeholder={t('tenant.completeProfile.zipPlaceholder')}
                        onKeyPress={(e) => handleCepKeyPress(e, setData)}
                        maxLength={9}
                        className="flex-1"
                    />
                    <Button
                        type="button"
                        onClick={() => handleSearchCep(setData)}
                        disabled={isSearchingCep}
                        variant="outline"
                        className="whitespace-nowrap"
                    >
                        {isSearchingCep ? 'Buscando...' : 'Buscar CEP'}
                    </Button>
                </div>
                {cepError && (
                    <p className="text-sm text-destructive">{cepError}</p>
                )}
                <InputError message={errors.zip} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="address">{t('tenant.completeProfile.addressLabel')}</Label>
                <Input
                    ref={addressRef}
                    id="address"
                    name="address"
                    value={data.address}
                    onChange={(e) => {
                        setData('address', e.target.value);
                        if (errors.address) {
                            clearErrors('address');
                        }
                    }}
                    placeholder={t('tenant.completeProfile.addressPlaceholder')}
                />
                <InputError message={errors.address} />
            </div>

            <div className="grid gap-6 md:grid-cols-3">
                <div className="grid gap-2">
                    <Label htmlFor="neighborhood">
                        {t('tenant.completeProfile.neighborhoodLabel')}
                    </Label>
                    <Input
                        ref={neighborhoodRef}
                        id="neighborhood"
                        name="neighborhood"
                        value={data.neighborhood}
                        onChange={(e) => {
                            setData('neighborhood', e.target.value);
                            if (errors.neighborhood) {
                                clearErrors('neighborhood');
                            }
                        }}
                        placeholder={t('tenant.completeProfile.neighborhoodPlaceholder')}
                    />
                    <InputError
                        message={errors.neighborhood}
                    />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="state">{t('tenant.completeProfile.stateLabel')}</Label>
                    <Select
                        value={selectedEstado ? selectedEstado : undefined}
                        onValueChange={(value) => {
                            handleEstadoChange(value);
                            setData('state', value);
                            if (errors.state) {
                                clearErrors('state');
                            }
                        }}
                        disabled={isLoadingEstados}
                    >
                        <SelectTrigger
                            id="state"
                            ref={stateSelectRef}
                        >
                            <SelectValue placeholder={t('tenant.completeProfile.statePlaceholder')} />
                        </SelectTrigger>
                        <SelectContent>
                            {estados.map((estado) => (
                                <SelectItem
                                    key={estado.sigla}
                                    value={estado.sigla}
                                >
                                    {estado.nome}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <InputError message={errors.state} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="city">{t('tenant.completeProfile.cityLabel')}</Label>
                    <Select
                        value={selectedCidadeCodigo ? selectedCidadeCodigo : undefined}
                        onValueChange={(value) => {
                            setSelectedCidadeCodigo(value);
                            setData('city', findCidadeNameByCodigo(value));
                            if (errors.city) {
                                clearErrors('city');
                            }
                        }}
                        disabled={!selectedEstado || isLoadingCidades}
                    >
                        <SelectTrigger
                            id="city"
                            ref={citySelectRef}
                        >
                            <SelectValue placeholder={t('tenant.completeProfile.cityPlaceholder')} />
                        </SelectTrigger>
                        <SelectContent>
                            {cidades.map((cidade) => (
                                <SelectItem
                                    key={cidade.codigo_ibge}
                                    value={cidade.codigo_ibge}
                                >
                                    {cidade.nome}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <InputError message={errors.city} />
                </div>
            </div>

            <div className="grid gap-2">
                <Label htmlFor="country">{t('tenant.completeProfile.countryLabel')}</Label>
                <Input
                    id="country"
                    name="country"
                    value={data.country}
                    onChange={(e) => {
                        setData('country', e.target.value);
                        if (errors.country) {
                            clearErrors('country');
                        }
                    }}
                    placeholder={t('tenant.completeProfile.countryPlaceholder')}
                />
                <InputError message={errors.country} />
            </div>

            <div className="flex items-center gap-4">
                <Button
                    type="submit"
                    disabled={processing}
                    data-test="update-tenant-button"
                >
                    {processing
                        ? t('tenant.completeProfile.saving')
                        : t('tenant.completeProfile.save')}
                </Button>

                <Transition
                    show={recentlySuccessful}
                    enter="transition ease-in-out"
                    enterFrom="opacity-0"
                    leave="transition ease-in-out"
                    leaveTo="opacity-0"
                >
                    <p className="text-sm text-neutral-600">
                        {t('tenant.completeProfile.saved')}
                    </p>
                </Transition>
            </div>
        </form>
    );
}

