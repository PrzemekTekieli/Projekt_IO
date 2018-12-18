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
    pieniadze       integer
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

insert into Gracze (login, haslo) values
('jerry', 'kittens'),
('bla_cack', '$tudent'),
('komornik', 'pienionszki'),
('kurnik', 'kogut'),
('sweetie', 'chocco');

insert into Lokacje (id_lokacji, x, y, opis) values
(1, 0, 0, 'Lokacja startowa'),
(2, 0, 1, 'Trochę obok'),
(3, 1, 0, 'W drugą stronę'),
(4, 1, 1, 'Na ukos');

insert into Statystyka (id_statystyki, atak, obrona, hp) values
(1, 1, 1, 1),
(2, 2, 3, 10),
(3, 40, 35, 100),
(4, 42, 5, 12),
(5, 3, 3, 3);

insert into Kategoria (id_kategorii, nazwa, opis) values
(1, 'Naramiennik', 'Coś co zakładasz dla ochrony kończyn górnych.'),
(2, 'Buty', 'Zakładasz na stopy.'),
(3, 'Rękawice', 'Zakładasz na dłonie.'),
(4, 'Spodnie', 'Zakładasz na nogi.'),
(5, 'Hełm', 'Zakładasz na łeb i masz zakuty.');

insert into Postacie values
(1, 'jerry', 1, 1, 'Alduin'),
(2, 'jerry', 1, 2, 'Bezimienny'),
(3, 'bla_cack', 4, 3, 'Geralt'),
(4, 'komornik', 2, 4, 'Lars'),
(5, 'bla_cack', 3, 5, 'Eldric');

insert into Ekwipunek values
(1, 1, 2500),
(2, 2, 100),
(3, 3, 42),
(4, 4, 5),
(5, 5, 8219);

insert into NPC values
(1, 1, true, 'John'),
(2, 2, false, 'David'),     
(3, 3, false, 'Mark'),
(4, 4, false, 'Zenon'),
(5, 4, false, 'Nastain');

insert into Potwory values
(1, 1, 'Krowa', 3),
(2, 2, 'Konik', 4),
(3, 3, 'Smok', 456),
(4, 4, 'Goryl', 523),
(5, 5, 'Kuc', 23);

insert into Przedmiot values
(1, 1, 2, 'Super buty mocy', 234, 'Super mocne.'),
(2, 2, 3, 'Rozwalone rękawice', 1, 'Praktycznie bezużyteczne.'),
(3, 3, 5, 'Garnek', 2, 'Zawsze coś.'),
(4, 4, 3, 'Kolczaste rękawice', 200, 'Drap z rozwagą!'),
(5, 5, 1, 'Naramienniki Pradawnego Boga', 10000, 'Tylko dla wyznawców.');

insert into Wystapienia values
(2, 1, 25),
(3, 4, 80),
(3, 2, 34),
(3, 3, 99),
(4, 1, 10);

insert into Zawartosc values
(1, 1, 23),
(4, 3, 43),
(3, 3, 1),
(2, 2, 14),
(1, 2, 3);

commit;
