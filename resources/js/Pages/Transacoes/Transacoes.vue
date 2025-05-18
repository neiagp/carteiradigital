<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import { Head, usePage, router } from '@inertiajs/vue3'
import axios from 'axios'
import vSelect from 'vue3-select'
import 'vue3-select/dist/vue3-select.css';

const page = usePage()
const transacoes = ref(page.props.transacoes ?? [])
const saldo = ref(0)

const modo = ref(null)
const destinatarioId = ref(null)
const valor = ref(null)
const usuarios = ref([])
const buscandoUsuarios = ref(false)
const toast = ref(null)

const usuarioLogadoId = ref(page.props.auth.user.id)
const isAdmin = ref(page.props.auth.user.is_admin)

const fecharModal = () => cancelar()

const escListener = (e) => {
  if (e.key === 'Escape') fecharModal()
}

const modalReverterAberto = ref(false);
const transacaoParaReverter = ref(null);

const abrirModalReverter = (transacao) => {
  transacaoParaReverter.value = transacao;
  modalReverterAberto.value = true;
};

const fecharModalReverter = () => {
  modalReverterAberto.value = false;
  transacaoParaReverter.value = null;
};

const confirmarReversao = async () => {
  if (transacaoParaReverter.value) {
    await reverterTransacao(transacaoParaReverter.value.id);
    fecharModalReverter();
  }
};

onMounted(() => {
  document.addEventListener('keydown', escListener)
  calcularSaldo()
})

onBeforeUnmount(() => document.removeEventListener('keydown', escListener))

/*
  Função para atualizar o saldo sempre que as transações mudarem.
  Calcula o saldo total do usuário logado.  
*/
watch(() => page.props.transacoes, (newTransacoes) => {
  transacoes.value = newTransacoes;
  calcularSaldo();
});

/*
  Função para buscar usuários com base no termo de pesquisa.
  Faz uma requisição GET para a rota de busca de usuários.
  Atualiza a lista de usuários e o estado de carregamento.
*/
async function buscarUsuarios(termo) {
  console.log('Buscar usuários chamado com termo:', termo);
  if (termo !== undefined && termo !== null && termo.length < 2 && termo !== '') {
    usuarios.value = [];
    console.log('Usuarios resetado:', usuarios.value);
    return;
  }
  buscandoUsuarios.value = true;
  try {
    const { data } = await axios.get('/api/usuarios', {
      params: { q: termo }
    });
    console.log('Dados da API recebidos:', data);
    usuarios.value = data;
    console.log('Usuarios atualizado:', usuarios.value);
  } catch (error) {
    showToast('Erro ao buscar usuários', 'erro');
    console.error('Erro ao buscar usuários:', error);
  } finally {
    buscandoUsuarios.value = false;
    console.log('Busca finalizada, buscandoUsuarios:', buscandoUsuarios.value);
  }
}

/*
  Função para buscar usuários quando o modo de transferência é ativado.
  Limpa a lista de usuários e o destinatário selecionado ao fechar o modal.
*/
watch(modo, (novoModo) => {
  if (novoModo === 'transferencia') {
    buscarUsuarios(''); // Busca inicial ao abrir o modal
  } else {
    usuarios.value = [];
    destinatarioId.value = null;
  }
});

/* 
  Função para confirmar a transação.
  Verifica se o valor e o destinatário foram informados.
  Faz uma requisição POST para a rota de depósito ou transferência.
*/
async function confirmar() {
  if (modo.value === 'transferencia' && !destinatarioId.value) {
    showToast('Selecione um destinatário', 'erro');
    return;
  }
  if (!valor.value) {
    showToast('Informe o valor da transação', 'erro');
    return;
  }

  const rota = modo.value === 'transferencia'
    ? '/api/transferencia'
    : '/api/deposito'

  const payload = {
    valor: valor.value,
  };

  if (modo.value === 'transferencia') {
    payload.destinatario_id = destinatarioId.value;
  }

  try {
    await axios.post(rota, payload);
    showToast('Transação realizada com sucesso 🎉');
    cancelar();
    router.reload({ only: ['transacoes', 'saldo'] });
  } catch (error) {
    if (error.response && error.response.data && error.response.data.erro) {
      const erroData = error.response.data.erro;
      if (typeof erroData === 'string') {
        showToast(`Erro ao realizar transação: ${erroData}`, 'erro');
      } else if (Array.isArray(erroData)) {
        showToast(`Erro ao realizar transação:\n${erroData.join('\n')}`, 'erro');
      } else if (typeof erroData === 'object' && erroData !== null) {
        let mensagemErroCompleta = '';
        for (const campo in erroData) {
          if (erroData.hasOwnProperty(campo)) {
            mensagemErroCompleta += `${campo}: ${Array.isArray(erroData[campo]) ? erroData[campo].join(', ') : erroData[campo]}\n`;
          }
        }
        showToast(`Erro ao realizar transação:\n${mensagemErroCompleta}`, 'erro');
      } else {
        showToast('Erro ao realizar transação.', 'erro'); // Formato de erro desconhecido
      }
    } else {
      showToast('Erro ao realizar transação.', 'erro');
    }
  }
}

/*
  Função para reverter uma transação.
  Faz uma requisição POST para a rota de reversão de transação.
*/
async function reverterTransacao(transacaoId) {
  try {
    await axios.post(`/api/reversao/${transacaoId}`)
    showToast('Transação revertida com sucesso 🎉')
    router.reload({ only: ['transacoes', 'saldo'] })
  } catch (error) {
    if (error.response && error.response.data && error.response.data.erro) {
      const erroData = error.response.data.erro;
      if (typeof erroData === 'string') {
        showToast(`Erro ao realizar transação: ${erroData}`, 'erro');
      } else if (Array.isArray(erroData)) {
        showToast(`Erro ao realizar transação:\n${erroData.join('\n')}`, 'erro');
      } else if (typeof erroData === 'object' && erroData !== null) {
        let mensagemErroCompleta = '';
        for (const campo in erroData) {
          if (erroData.hasOwnProperty(campo)) {
            mensagemErroCompleta += `${campo}: ${Array.isArray(erroData[campo]) ? erroData[campo].join(', ') : erroData[campo]}\n`;
          }
        }
        showToast(`Erro ao realizar transação:\n${mensagemErroCompleta}`, 'erro');
      } else {
        showToast('Erro ao realizar transação.', 'erro'); // Formato de erro desconhecido
      }
    } else {
      showToast('Erro ao reverter transação.', 'erro');
    }
  }
}

/*
  Função para calcular o saldo total do usuário logado
  com base nas transações realizadas.
*/
function calcularSaldo() {
  if (!transacoes.value || !Array.isArray(transacoes.value)) return

  let total = 0
  transacoes.value.forEach(t => {
    if (t.revertida) return
    if (t.tipo === 'deposito' && t.destinatario_id === usuarioLogadoId.value) {
      total += Number(t.valor)
    } else if (t.tipo === 'transferencia') {
      if (t.remetente_id === usuarioLogadoId.value) total -= Number(t.valor)
      if (t.destinatario_id === usuarioLogadoId.value) total += Number(t.valor)
    }
  })
  saldo.value = total
}

/*
  Função para cancelar a operação atual.
  Limpa os campos de entrada e fecha o modal.
*/
function cancelar() {
  modo.value = null
  destinatarioId.value = null
  valor.value = null
  usuarios.value = []
}

/*
  Função para exibir um toast com uma mensagem.
  O tipo pode ser 'sucesso' ou 'erro'.
*/
function showToast(msg, tipo = 'sucesso') {
  toast.value = { msg, tipo }
  setTimeout(() => (toast.value = null), 3000)
}

</script>

<template>

  <Head title="Transações" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Transações
      </h2>
    </template>

    <div class="max-w-5xl mx-auto p-6 bg-white shadow rounded">

      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">
          Saldo: <span class="text-emerald-600">R$ {{ Number(saldo).toFixed(2) }}</span>
        </h1>
        <div class="flex gap-2">
          <button @click="modo = 'deposito'" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700">
            Depositar
          </button>
          <button @click="modo = 'transferencia'" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Transferir
          </button>
        </div>
      </div>

      <h2 class="text-xl font-semibold mb-2">Extrato de Transações</h2>
      <div class="overflow-x-auto">
        <table class="w-full text-left border bg-white">
          <thead class="bg-gray-100">
            <tr>
              <th class="border px-3 py-2">Tipo</th>
              <th class="border px-3 py-2">Valor</th>
              <th class="border px-3 py-2">De</th>
              <th class="border px-3 py-2">Para</th>
              <th class="border px-3 py-2">Data</th>
              <th class="border px-3 py-2">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in transacoes" :key="t.id">
              <td class="border px-3 py-2 capitalize">{{ t.tipo }}</td>
              <td class="border px-3 py-2 text-right">
                R$ {{ Number(t.valor).toFixed(2) }}
              </td>
              <td class="border px-3 py-2">{{ t.remetente?.name ?? '-' }}</td>
              <td class="border px-3 py-2">{{ t.destinatario?.name ?? '-' }}</td>
              <td class="border px-3 py-2">
                {{ new Date(t.created_at).toLocaleString('pt-BR') }}
              </td>
              <td class="border px-3 py-2">
                <template v-if="!t.revertida">
                  <button v-if="(t.tipo === 'transferencia' && t.remetente_id === usuarioLogadoId) || isAdmin"
                    @click="abrirModalReverter(t)"
                    class="text-red-600 hover:text-red-700 focus:outline-none flex items-center gap-1" title="Reverter">
                    <svg class="h-5 w-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h12a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                    </svg>
                    <span class="sr-only">Reverter</span>
                  </button>
                  <span v-else class="text-gray-500"></span>
                </template>
                <template v-if="t.revertida">
                  <span v-if="(t.tipo === 'transferencia' && t.remetente_id !== usuarioLogadoId)" class="text-gray-500">Revertido</span>
                  <span v-else class="text-gray-500">Revertido</span>
                </template>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="modo" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
      @click.self="fecharModal">
      <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg relative">
        <h2 class="text-lg font-semibold mb-4">
          {{ modo === 'transferencia' ? 'Transferir' : 'Depositar' }}
        </h2>

        <div v-if="modo === 'transferencia'" class="mb-4">
          <label class="block text-sm font-medium mb-1">Destinatário</label>
          <v-select :key="modo === 'transferencia' ? 'transferencia-select' : 'null-select'" style="z-index: 999999;"
            v-model="destinatarioId" :options="usuarios" :reduce="user => user.id"
            :get-option-label="user => `${user.name} - ${user.email}`" :filterable="true" :searchable="true"
            :loading="buscandoUsuarios" :clearable="false" placeholder="Digite para buscar destinatário"
            @search="buscarUsuarios">
            <template #no-options>
              <span class="text-gray-500">Nenhum usuário encontrado</span>
            </template>

            <template #option="{ option }">
              <div v-if="option && option.name && option.email" class="flex flex-col" style="z-index: 99999;">
                <span class="font-medium">{{ option.name }}</span>
                <span class="text-sm text-gray-500">{{ option.email }}</span>
              </div>
            </template>
          </v-select>
          <pre>{{ JSON.stringify(usuarios.value, null, 2) }}</pre>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Valor</label>
          <input type="number" step="0.01" min="0.01" v-model.number="valor" class="border rounded w-full p-2"
            placeholder="Ex: 150.00" />
        </div>

        <div class="flex justify-end gap-3 mt-2">
          <button @click="confirmar" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
            Confirmar
          </button>
          <button @click="cancelar" class="text-gray-600 px-4 py-2 rounded hover:underline">
            Cancelar
          </button>
        </div>
      </div>
    </div>

    <div v-if="modalReverterAberto" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
      @click.self="fecharModalReverter">
      <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg relative">
        <h2 class="text-lg font-semibold mb-4">Confirmar Reversão</h2>
        <p class="mb-4" v-if="transacaoParaReverter">
          Tem certeza que deseja reverter a transação no valor de
          <span class="font-bold text-red-600">R$ {{ Number(transacaoParaReverter.valor).toFixed(2) }}</span>?
          <br>
          <span class="text-sm text-gray-500">Esta ação não poderá ser desfeita.</span>
        </p>
        <p class="mb-4" v-else>
          Erro ao exibir informações da transação para reversão.
          Por favor, tente novamente.
        </p>
        <div class="flex justify-end gap-3 mt-4">
          <button @click="confirmarReversao" :disabled="!transacaoParaReverter"
            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 focus:outline-none">
            Confirmar Reversão
          </button>
          <button @click="fecharModalReverter"
            class="text-gray-600 px-4 py-2 rounded hover:underline focus:outline-none">
            Cancelar
          </button>
        </div>
      </div>
    </div>



    <div v-if="toast"
      class="fixed bottom-5 left-1/2 transform -translate-x-1/2 bg-white border px-4 py-2 rounded shadow-lg z-50"
      :class="toast.tipo === 'erro' ? 'border-red-500 text-red-600' : 'border-emerald-500 text-emerald-700'">
      {{ toast.msg }}
    </div>
  </AuthenticatedLayout>
</template>

<style src="vue3-select/dist/vue3-select.css"></style>
<style>
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}
</style>