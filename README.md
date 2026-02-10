# WooSync

Hi I am Martin, carpenter, noob coder. This is my project is in its early stage, i.e. not finished. 
The final objective is to create funcional plugin for FacturaScripts (facturascripts.com), 
which syncs products, stock, clients, and orders from WooCommerce into Facturascripts.

Condiciones actuales: WooCommerce 10.4.3 / WordPress 6.9 está en el mismo shared server con Facturascripts 2025.71 - 
No tengo acceso a CLI. 
El objetivo de funcionamiento de WooSync, que aparezcan en FacturaScripts clientes y pedidos de WooCommerce, no está logrado aun. 

Configuración
URL de WooCommerce
Consumer Key
Consumer Secret

Botón manual
“Sincronizar ahora”

Tienda pública
Acceso público en: /index.php?page=WooSyncStore
Permite buscar y listar productos registrados en FacturaScripts.

Qué sincroniza
productos → productos
clientes → clientes
pedidos → pedidos
stock → stock / opcional

Flujo (paso a paso)
FacturaScripts
   ↓ (PHP)
Llama a WooCommerce API
   ↓
Recibe JSON
   ↓
Crea / actualiza:
   - clientes
   - productos (+stock)
   - pedidos


     
