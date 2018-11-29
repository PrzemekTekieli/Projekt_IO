drop table Gracze cascade;
drop table Ekwipunek cascade;
drop table Kategoria cascade;
drop table Lokacje cascade;
drop table Misje cascade;
drop table NPC cascade;
drop table Postacie cascade;
drop table Potwory cascade;
drop table Przedmiot cascade;
drop table Statystyka cascade;
drop table Wystapienia cascade;
drop table Zawartosc cascade;

begin;

create table Gracze (
    id_gracza   serial primary key,
    login       varchar(15) not null,
    haslo       varchar(15) not null
);

create table Lokacje (
    id_lokacji  serial primary key,
    x           integer not null,
    y           integer not null,
    opis        varchar(500)
);

create table Statystyka (
    id_statystyki   serial primary key,
    atak            integer not null,
    obrona          integer not null,
    hp              integer not null
);

create table Kategoria (
    id_kategorii    serial primary key,
    nazwa           varchar(20),
    opis            varchar(500)
);

create table Postacie (
    id_postaci      serial primary key,
    id_gracza       integer not null references Gracze,
    id_lokacji      integer not null references Lokacje,
    id_statystyki   integer not null references Statystyka,
    nazwa           varchar(20)
);

create table Ekwipunek (
    id_ekwipunku    serial primary key,
    id_postaci      integer not null references Postacie,
    pieniądze       integer
);

create table NPC (
    id_ekwipunku    integer not null references Ekwipunek,
    id_lokacji      integer not null references Lokacje,
    zleceniodawca   boolean,
    nazwa           varchar(20),
    primary key (id_ekwipunku, id_lokacji)
);

create table Potwory (
    id_potwora      serial primary key,
    id_statystyki   integer not null references Statystyka,
    nazwa           varchar(20),
    pieniadze       integer
);

create table Przedmiot (
    id_przedmiotu   serial primary key,
    id_statystyki   integer not null references Statystyka,
    id_kategorii    integer not null references Kategoria,
    nazwa           varchar(200),
    cena            integer,
    opis            varchar(500)
);

create table Wystapienia (
    id_lokacji          integer not null references Lokacje,
    id_potwora          integer not null references Potwory,
    procent_odrodzenia  integer,
    primary key (id_lokacji, id_potwora)
);

create table Zawartosc (
    id_przedmiotu   integer not null references Przedmiot,
    id_ekwipunku    integer not null references Ekwipunek,
    ilosc           integer,
    primary key (id_przedmiotu, id_ekwipunku)
);

create table Misje (
    id_misji                serial primary key,
    opis                    varchar(500),
    id_docelowej_lokacji    integer not null references Lokacje,
    id_potwora_do_zabicia   integer not null references Potwory,
    iloscDoZabicia          integer,
    pieniadze               integer,
    id_nagrody              integer not null references Przedmiot
);

copy Gracze from stdin with (null '', delimiter '|');
1|jerry|kittens
2|bla_cack|$tudent
3|komornik|pienionszki
4|kurnik|kogut
5|sweetie|chocco
\.

copy Lokacje from stdin with (null '', delimiter '|');
1|0|0|Lokacja startowa
2|0|1|Trochę obok
3|1|0|W drugą stronę
4|1|1|Na ukos
\.

copy Statystyka from stdin with (null '', delimiter '|');
1|1|1|1
2|2|3|10
3|40|35|100
4|42|5|12
5|3|3|3
\.

copy Kategoria from stdin with (null '', delimiter '|');
1|Naramiennik|Coś co zakładasz dla ochrony kończyn górnych.
2|Buty|Zakładasz na stopy.
3|Rękawice|Zakładasz na dłonie.
4|Spodnie|Zakładasz na nogi.
5|Hełm|Zakładasz na łeb i masz zakuty.
\.

copy Postacie from stdin with (null '', delimiter '|');
1|1|1|1|Alduin
2|1|1|2|Bezimienny
3|2|4|5|Geralt
4|3|2|3|Lars
5|2|3|2|Eldric
\.

copy Ekwipunek from stdin with (null '', delimiter '|');
1|1|2500
2|2|100
3|3|42
4|4|5
5|5|8219
\.

copy NPC from stdin with (null '', delimiter '|');
1|1|true|John
2|2|false|David     
3|3|false|Mark
4|4|false|Zenon
5|4|false|Nastain
\.

copy Potwory from stdin with (null '', delimiter '|');
1|2|Krowa|3
2|2|Konik|4
3|3|Smok|456
4|1|Goryl|523
5|4|Kuc|23
\.

copy Przedmiot from stdin with (null '', delimiter '|');
1|3|2|Super buty mocy|234|Super mocne.
2|1|3|Rozwalone rękawice|1|Praktycznie bezużyteczne.
3|3|5|Garnek|2|Zawsze coś.
4|1|3|Kolczaste rękawice|200|Drap z rozwagą!
5|1|1|Naramienniki Pradawnego Boga|10000|Tylko dla wyznawców.
\.

copy Wystapienia from stdin with (null '', delimiter '|');
2|1|25
3|4|80
3|2|34
3|3|99
4|1|10
\.

copy Zawartosc from stdin with (null '', delimiter '|');
1|1|23
4|3|43
3|3|1
2|2|14
1|2|3
\.

copy Misje from stdin with (null '', delimiter '|');
1|Musisz iść do Baśniowego Boru i wrócić po nagrodę.|4|2|0|200|2
2|Zabij jednonogiego pirata!|3|2|1|300|3
3|Przynieś mi 30 marchewek.|3|3|0|10|4
4|Odnajdź Kraniec Świata i wróć po nagrodę.|3|4|0|1000|4
5|Zabij Złotego Skarabeusza!|2|5|1|30023|3
\.

commit;
