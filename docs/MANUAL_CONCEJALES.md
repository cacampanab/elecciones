# Manual de Concejales y D'Hondt

## ¿Para qué sirve?

El módulo **Concejales** permite:

- Registrar las listas de concejales urbanos y rurales.
- Registrar los integrantes de cada lista en el orden de la papeleta.
- Registrar los votos obtenidos por cada lista.
- Calcular la distribución de escaños con el método D'Hondt.
- Ver qué candidatos resultan electos según el orden de cada lista.

D'Hondt se aplica únicamente a **Concejal Urbano** y **Concejal Rural**. No se aplica a Alcalde, Prefecto ni Presidente.

## Antes de empezar

### 1. Configurar los escaños

Ir a:

**Administración → Escaños**

Seleccionar la elección y configurar por cantón:

- Cantidad de concejales urbanos.
- Cantidad de concejales rurales.

Ejemplo para el cantón Esmeraldas:

- Concejal Urbano: 9
- Concejal Rural: 2

Los concejales urbanos y rurales son bolsas separadas. No se suman.

### 2. Verificar partidos y candidatos

Los partidos deben existir en **Administración → Partidos**.

Los integrantes de las listas de concejales se agregan desde el módulo **Concejales**, no desde el módulo general de candidatos.

## Uso del módulo

Ir a:

**Resultados → Concejales / D'Hondt**

### 1. Seleccionar elección, cantón y dignidad

En la parte superior seleccionar:

- Elección.
- Cantón.
- Dignidad: Concejal Urbano o Concejal Rural.

El sistema mostrará cuántos escaños están configurados para esa combinación.

Si aparece **0 escaños configurados**, primero hay que configurarlos en **Administración → Escaños**.

### 2. Agregar integrantes a las listas

En la sección **Agregar integrante a una lista**:

1. Seleccionar el partido o lista.
2. Escribir nombres y apellidos.
3. Opcionalmente, cédula.
4. Indicar el orden en la papeleta.
5. Presionar **Agregar**.

Si no se indica orden, el candidato se agrega al final de la lista.

La lista del partido se crea automáticamente para esa elección, cantón y dignidad.

### 3. Ordenar y corregir integrantes

En la sección **Listas e integrantes** se muestra cada lista con sus candidatos.

Se puede:

- Cambiar el orden.
- Corregir nombres, apellidos o cédula.
- Eliminar un integrante.
- Guardar los cambios con **Guardar nombres y orden**.

El orden es importante porque los escaños se asignan en ese orden: primero el candidato 1, luego el 2, y así sucesivamente.

### 4. Registrar los votos en el acta de cada JRV

Los votos de concejales se registran en:

**Resultados → Registrar Resultados**

Al seleccionar una JRV y la dignidad **Concejal Urbano** o **Concejal Rural**, el formulario muestra las listas del cantón con un campo de votos por lista.

También se registran:

- Electores del acta.
- Total de sufragantes.
- Votos blancos.
- Votos nulos.

El sistema valida que:

- Votos de listas + blancos + nulos = total de sufragantes.
- Los sufragantes no superen a los electores.
- Los votos se guarden por JRV.

### 5. Calcular escaños

En **Resultados → Concejales / D'Hondt**:

- Los votos se toman automáticamente de las actas registradas por JRV.
- Presionar **Recalcular escaños**.

## Cómo se calculan los escaños

Para cada dignidad, el sistema:

1. Toma los votos de cada lista.
2. Divide los votos de cada lista entre 1, 2, 3, 4… hasta el número de escaños.
3. Ordena todos los cocientes de mayor a menor.
4. Asigna un escaño por cada cociente hasta completar los escaños disponibles.
5. Entrega los escaños a los candidatos de cada lista, en el orden registrado.

### Ejemplo

Para 2 escaños:

- Lista A: 100 votos → cocientes 100, 50
- Lista B: 60 votos → cocientes 60, 30
- Lista C: 40 votos → cocientes 40, 20

Los dos cocientes más altos son 100 y 60. Por lo tanto:

- Lista A: 1 escaño.
- Lista B: 1 escaño.
- Lista C: 0 escaños.

## Desempate

Si dos cocientes son iguales, el sistema usa este orden:

1. Mayor cantidad de votos actuales de la lista.
2. Menor número de lista.

## Advertencia importante: listas con menos candidatos que escaños

Puede ocurrir que una lista gane más escaños que la cantidad de candidatos que tiene.

Ejemplo:

- Una lista gana 6 escaños.
- Pero solo tiene 5 candidatos.

En ese caso el sistema muestra una alerta y deja constancia de que falta 1 candidato. No inventa ni repite candidatos. La regla para completar ese escaño debe venir del CNE o del reglamento electoral.

## Diferencia entre urbano y rural

- **Concejal Urbano:** se calcula solo con listas y votos urbanos.
- **Concejal Rural:** se calcula solo con listas y votos rurales.

No se mezclan votos ni candidatos entre las dos dignidades.

## Alcance de esta versión

Esta versión registra los **votos por lista en cada acta de JRV** y luego consolida esos votos por cantón y dignidad para calcular D’Hondt.

Los votos de concejales se cargan desde:

**Resultados → Registrar Resultados**

La validación de consistencia del acta se hace con la suma de votos de las listas más blancos y nulos.

## Alcalde por cantón

El Alcalde también es una dignidad cantonal. Para registrarlo:

1. Ir a **Administración → Candidatos**.
2. Crear o editar el candidato.
3. Seleccionar la dignidad **Alcalde**.
4. Aparecerán los campos **Provincia** y **Cantón**.
5. Seleccionar la provincia y luego el cantón.
6. Guardar.

El sistema no permite guardar un Alcalde sin provincia y cantón.

En la importación de Excel se agregaron las columnas:

- Provincia
- Cantón

Son obligatorias cuando la dignidad es **Alcalde**.

## Resumen rápido

1. Configurar escaños.
2. Agregar integrantes a cada lista en orden.
3. Registrar las actas por JRV en **Resultados → Registrar Resultados**.
4. Ir a **Resultados → Concejales / D'Hondt**.
5. Revisar los votos consolidados desde las actas.
6. Presionar **Recalcular escaños**.
7. Revisar escaños y candidatos electos.
