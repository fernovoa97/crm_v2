<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CacSeeder extends Seeder
{
    public function run(): void
{
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    DB::table('cacs')->truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    DB::table('cacs')->insert([
            ['nombre' => 'CAC AYACUCHO', 'direccion' => 'JR. CUZCO 220 - AYACUCHO - HUAMANGA - AYACUCHO', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC HUANCAYO', 'direccion' => 'CENTRO COMERCIAL REAL PLAZA HUANCAYO, LOCAL LS-01  - CRUCE DE LAS AV. GIRALDES Y FERROCARRIL -HUANCAYO - HUANCAYO - JUNIN', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC HUANUCO', 'direccion' => 'CENTRO COMERCIAL REAL PLAZA, LOCAL B06 - JR. INDEPENDENCIA 1799 - HUÁNUCO - HUÁNUCO - HUÁNUCO', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC ICA', 'direccion' => 'AV. SAN MARTIN 564, URBANIZACIÓN LAS MORALES, ICA – ICA – ICA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC IQUITOS', 'direccion' => 'CALLE TACNA 570 - LORETO - MAYNAS - IQUITOS', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC PUCALLPA', 'direccion' => 'JR. CORONEL PORTILLO 586-588 - CALLERIA – CORONEL PORTILLO – UCAYALI', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC TARAPOTO', 'direccion' => 'JR. JIMÉNEZ PIMENTEL N°  232 – TARAPOTO - SAN MARTIN - SAN MARTIN', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC AVIACIÓN', 'direccion' => 'Aviación 2639, San Borja', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC BEGONIAS', 'direccion' => 'AV. LAS BEGONIAS N°798 - ESQUINA CON RIVERA NAVARRETE - SAN ISIDRO - LIMA - LIMA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC CENTRO CIVICO', 'direccion' => 'CENTRO COMERCIAL REAL PLAZA - CENTRO  CIVICO,  EDF L1 INT 2040 - AV. INCA GARCILAZO DE LA VEGA 1337 CERCADO DE LIMA - LIMA - LIMA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC CHORRILLOS', 'direccion' => 'CENTRO COMERCIAL PLAZA LIMA SUR, LOCAL LI - 222A - AV. PASEO DE LA REPÚBLICA 5000 - MATELLINI - CHORRILLOS - LIMA - LIMA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC JOCKEY PLAZA', 'direccion' => 'CENTRO COMERCIAL JOCKEY PLAZA, PISO 2 - ZONA BARRIO TIENDA 218/219. AV. JAVIER PRADO ESTE 4200 - SANTIAGO DE SURCO', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC LA MOLINA', 'direccion' => 'AV. RAUL FERRERO REBAGLIATI NRO. 1354 - URB. EL REMANSO I ETAPA -LA MOLINA - LIMA - LIMA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC LARCO', 'direccion' => 'AV. LARCO N° 652 - MIRAFLORES - LIMA - LIMA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC MALL DEL SUR', 'direccion' => 'CENTRO COMERCIAL MALL DEL SUR, SUB LOTE 1- LOCAL COMERCIAL #TMS 3054- 3058 – AVENIDA LOS LIRIOS N° 301 CALLE CARRETERA ATOCONGO – SAN JUAN DE MIRAFLORES - LIMA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC MINKA', 'direccion' => 'CENTRO COMERCIAL MINKA, LOCAL L428 - CALLE 3 - AV. ARGENTINA N° 3093 - BELLAVISTA - CALLAO - LIMA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CACE PLAZA NORTE', 'direccion' => 'CENTRO COMERCIAL  PLAZA LIMA NORTE – CENTRO COMERCIAL PLAZA NORTE Av. ALFREDO MENDIOLA 1400 INDEPENDENCIA LOCAL LI-329 3er PISO', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC RIVERA NAVARRETE', 'direccion' => 'AV. RIVERA NAVARRETE 758 - SAN ISIDRO - LIMA - LIMA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC SALAVERRY', 'direccion' => 'CENTRO COMERCIAL REAL PLAZA SALAVERRY, LOCAL LS-09 -  AV. FELIPE SALAVERRY S/N CDRA. 24 - JESUS MARIA - LIMA - LIMA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC SAN JUAN DE LURIGANCHO', 'direccion' => 'CC Mall Aventura local B-3000, 3er piso SJL (costado de Tai Loy)', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC SAN MIGUEL', 'direccion' => 'CENTRO COMERCIAL PLAZA SAN MIGUEL LOCAL 303 AV.LA MARINA CDRA. 21 SAN MIGUEL –LIMA –LIMA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC SANTA ANITA', 'direccion' => 'CENTRO COMERCIAL MALL AVENTURA PLAZA, LOCAL FL-4A - CARRETERA CENTRAL 111 - SANTA ANITA - LIMA - LIMA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CACE SANTA CLARA', 'direccion' => 'CENTRO COMERCIAL REAL PLAZA, LOCALES 149-150-151 - AV. NICOLÁS AYLLON 8694 ', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC UNICACHI', 'direccion' => 'URB. PRO INDUSTRIAL AV. ALFREDO MENDIOLA 7026 Int.102', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC CAJAMARCA', 'direccion' => 'CENTRO COMERCIAL EL QUINDE - TIENDAS LC301,  LC303, LE120 - JR. SOR MANUELA GIL N° 151,URB. SAN CARLOS – CAJAMARCA - CAJAMARCA - CAJAMARCA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC CHICLAYO', 'direccion' => 'C.C. REAL PLAZA TIENDA N° 102 - AV. MIGUEL DE CERVANTES N° 300 - CHICLAYO - LAMBAYEQUE', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC CHICLAYO III Elias Aguirre', 'direccion' => 'JR. ELIAS AGUIRRE 766 - CHICLAYO - CHICLAYO - LAMBAYEQUE', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC CHIMBOTE 1', 'direccion' => 'JAV. JOSÉ GALVEZ N° 201 CHIMBOTE - CHIMBOTE - SANTA - ANCASH', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC PIURA REAL PLAZA', 'direccion' => 'CENTRO COMERCIAL REAL PLAZA PIURA, AV. SANCHEZ CERRO 234, DPTO. 239, LC 161 - PIURA - PIURA - PIURA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC PIURA OPEN PLAZA', 'direccion' => 'CENTRO COMERCIAL OPEN PLAZA, AV. ANDRES AVELINO CACERES 147 - URB. MIRAFLORES II PARTE (PISO 2) LC 107 - CASTILLA - CASTILLA - PIURA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC TRUJILLO', 'direccion' => 'MARISCAL ORBEGOSO N°  503/509 - TRUJILLO - TRUJILLO - LA LIBERTAD', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC TRUJILLO 3', 'direccion' => 'CENTRO COMERCIAL MALL AVENTURA PLAZA  Tda. 1029, 1033, 1037, 1041, 1047 AV AMERICA OESTE 750    - TRUJILLO - TRUJILLO - LA LIBERTAD', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC TUMBES', 'direccion' => 'CALLE PLAZA DE ARMAS 104, TUMBES - TUMBES - TUMBES', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC AREQUIPA', 'direccion' => 'AV. EL EJERCITO N° 701,YANAHUARA - AREQUIPA - AREQUIPA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC CERRO COLORADO', 'direccion' => 'CENTRO COMERCIAL MALL CENTER AREQUIPA – AV. AVIACION NRO. 602 AREQUIPA – AREQUIPA -  CERRO COLORADO', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC CUSCO', 'direccion' => 'AV. GARCILAZO  N°  1101,  ESQUINA CON  AV. EL SOL - CUSCO - CUSCO - CUSCO', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC CUSCO CENTRO', 'direccion' => 'CALLE AYACUCHO 227 - CUSCO - CUSCO - CUSCO', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC CUSCO REAL PLAZA', 'direccion' => 'C.C. CUSCO REAL PLAZA - SEMINARIO SAN ANTONIO A-2 , LOCAL 117/127 - CUSCO - CUSCO', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC ILO', 'direccion' => 'JR. CALLAO 707- ILO - ILO - MOQUEGUA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC JULIACA REAL PLAZA ', 'direccion' => 'CENTRO COMERCIAL REAL PLAZA ESQUINA TUMBES CON SAN MARTIN TDAS 101-102-103 - JULIACA - SAN ROMAN - PUNO', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC PORONGOCHE ', 'direccion' => 'CENTRO COMERCIAL MALL AVENTURA PLAZA AREQUIPA - AV. PORONGOCHE N° 500 - CRUCE CON CALLE RAYMONDI S/N - LS 02 / LS 03 / LS 04 - PAUCARPATA - AREQUIPA - AREQUIPA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CACE PUERTO MALDONADO', 'direccion' => 'AV. LEON VELARDE 586,  TAMBOPATA - TAMBOPATA - MADRE DE DIOS', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC PUNO', 'direccion' => 'JR. AREQUIPA 754 -  PUNO - PUNO', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC SAN ANDRES CUSCO', 'direccion' => 'CALLE SAN ANDRES 342 - CUSCO - CUSCO - CUSCO', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CAC TACNA', 'direccion' => 'CALLE ZELA 781 - TACNA - TACNA - TACNA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CACE PORONGOCHE II', 'direccion' => 'CENTRO COMERCIAL MALL AVENTURA PLAZA AREQUIPA - AV. PORONGOCHE N° 500 - CRUCE CON CALLE RAYMONDI S/N - LC SM -1-2-3-4 - PAUCARPATA - AREQUIPA - AREQUIPA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CACE LAMBRAMANI', 'direccion' => 'CENTRO COMERCIAL PARQUE LAMBRAMANI - AV. LAMBRAMANI 325 LC PM 2 INTERMEDIO-1ER PISO - CERCADO - AREQUIPA - AREQUIPA', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CACE JULIACA', 'direccion' => 'JR. MARIAÑO NUÑEZ 231 - JULIACA- SAN ROMAN - PUNO', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CACE HUARAZ', 'direccion' => 'AV. MARISCAL TORIBIO DE LUZURIAGA N° 523 GALERIAS – HUARAZ - HUARAZ - ANCASH', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'CACE TRUJILLO 2', 'direccion' => 'CENTRO COMERCIAL REAL PLAZA LC 164 AV FATINA SN URB EL GOLF', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
