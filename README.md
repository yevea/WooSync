# WooSync

Hi I am Martin, carpenter, noob coder. 
This is my project of plugin for FacturaScripts. 
The plugin shall sync products, clients, orders 
and stock from WooCommerce into Facturascripts.

WooCommerce está en el mismo servidor con Facturascripts. Todo debe poder 
gestionarse via cPanel, sin CLI. El objetivo de funcionamiento de WooSync
es que aparezcan en FacturaScripts clientes y pedidos de WooCommerce.

Configuración
URL de WooCommerce
Consumer Key
Consumer Secret

Botón manual
“Sincronizar ahora”

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


     
