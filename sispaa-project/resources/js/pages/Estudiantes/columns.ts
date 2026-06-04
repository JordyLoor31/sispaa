import type { ColumnDef } from '@tanstack/vue-table'
import { h } from 'vue'

export function makeColumns(viewStudent: (id: number) => void): ColumnDef<any, any>[] {
  return [
    { header: 'Estudiante', accessorKey: 'name', cell: info => info.getValue() },
    { header: 'Correo', accessorKey: 'email', cell: info => info.getValue() },
    { header: 'Cédula', accessorKey: 'cedula', cell: info => info.getValue() },
    { header: 'Carrera', accessorKey: 'carrera_nombre', cell: info => info.getValue() ?? '—' },
    { header: 'Matrícula', accessorKey: 'matricula_estado', cell: info => info.getValue() ?? '—' },
    { header: 'Faltas', accessorKey: 'faltas_count', cell: info => String(info.getValue() ?? 0) },
    { header: 'Documentos', accessorKey: 'documentos_count', cell: info => String(info.getValue() ?? 0) },
    { header: 'Acciones', id: 'actions', cell: ({ row }) => h('button', { class: 'btn', onClick: () => viewStudent(row.original.id) }, 'Ver') },
  ];
}

export default makeColumns;
