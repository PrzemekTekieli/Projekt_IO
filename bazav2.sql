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
drop table Oferta cascade;
drop table Wykonane cascade;
drop table Poziomy cascade;

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
    id_misji        integer,
    poziom          integer not null,
    xp              integer not null,
    nazwa           varchar(20)
);

create table Ekwipunek (
    id_ekwipunku    serial primary key,
    id_postaci      integer not null references Postacie,
    pieniadze       integer
);

create table Potwory (
    id_potwora      serial primary key,
    id_statystyki   integer not null references Statystyka,
    nazwa           varchar(20),
    pieniadze       integer,
    xp              integer
);

create table Przedmiot (
    id_przedmiotu   serial primary key,
    id_statystyki   integer not null references Statystyka,
    id_kategorii    integer not null references Kategoria,
    nazwa           varchar(200),
    wartosc         integer,
    opis            varchar(500)
);

create table NPC (
    id_NPC          serial primary key,
    id_lokacji      integer not null references Lokacje,
    zleceniodawca   boolean,
    nazwa           varchar(20),
    opis            varchar(500)
);

create table Oferta (
    id_NPC          integer not null references NPC,
    id_przedmiotu   integer not null references Przedmiot,
    cena            integer,
    primary key (id_NPC, id_przedmiotu)
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
    id_docelowej_lokacji    integer references Lokacje,
    id_potwora_do_zabicia   integer references Potwory,
    id_nagrody              integer references Przedmiot,
    id_NPC                  integer not null references NPC,
    ilosc_do_zabicia        integer,
    pieniadze               integer,
    xp                      integer,
    opis                    varchar(500)
);

create table Wykonane (
    id_misji    integer not null references Misje,
    id_postaci  integer not null references Postacie,  
    primary key (id_misji, id_postaci)
);

create table Poziomy (
    id_poziomu   serial primary key,
    wymagane_xp  integer
);

alter table Postacie add constraint misje_fk foreign key(id_misji) 
  references misje(id_misji);

insert into Gracze (login, haslo) values
('jerry', 'kittens'),
('bla_cack', '$tudent'),
('komornik', 'pienionszki'),
('kurnik', 'kogut'),
('sweetie', 'chocco');

insert into Lokacje (x, y, opis) values
(0, 0, 'Lokacja startowa'),
(0, 1, 'Trochę obok'),
(1, 0, 'W drugą stronę'),
(1, 1, 'Na ukos');

insert into Statystyka (atak, obrona, hp) values
(1, 1, 1),
(2, 3, 10),
(1000, 1000, 10000),
(42, 5, 12),
(3, 3, 3);

insert into Kategoria (nazwa, opis) values
('Naramiennik', 'Coś co zakładasz dla ochrony kończyn górnych.'),
('Buty', 'Zakładasz na stopy.'),
('Rękawice', 'Zakładasz na dłonie.'),
('Spodnie', 'Zakładasz na nogi.'),
('Hełm', 'Zakładasz na łeb i masz zakuty.');

insert into Postacie (id_gracza, id_lokacji, id_statystyki, nazwa, poziom, xp) values
(1, 1, 1, 'Alduin', 3, 22),
(1, 1, 2, 'Bezimienny', 2, 38),
(2, 4, 3, 'Geralt', 1, 0),
(3, 2, 4, 'Lars', 5, 100),
(2, 3, 5, 'Eldric', 3, 24);

insert into Ekwipunek (id_postaci, pieniadze) values
(1, 2500),
(2, 100),
(3, 42),
(4, 5),
(5, 8219);

insert into NPC (id_lokacji, zleceniodawca, nazwa, opis) values
(1, true, 'John', 'Stary i brzydki'),
(2, false, 'David', 'Zmęczony i stary'),     
(3, false, 'Mark', 'Po prostu Mark.'),
(4, false, 'Zenon', 'Tylko stary.'),
(4, false, 'Nastain', 'Z brodą.');

insert into Potwory (id_statystyki, nazwa, pieniadze, xp) values
(1, 'Krowa', 3, 1),
(2, 'Konik', 4, 3),
(3, 'Smok', 456, 200),
(4, 'Goryl', 523, 75),
(5, 'Kuc', 23, 2);

insert into Przedmiot (id_statystyki, id_kategorii, nazwa, wartosc, opis) values
(1, 2, 'Super buty mocy', 234, 'Super mocne.'),
(2, 3, 'Rozwalone rękawice', 1, 'Praktycznie bezużyteczne.'),
(3, 5, 'Garnek', 2, 'Zawsze coś.'),
(4, 3, 'Kolczaste rękawice', 200, 'Drap z rozwagą!'),
(5, 1, 'Naramienniki Pradawnego Boga', 10000, 'Tylko dla wyznawców.');

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

insert into Oferta values
(1, 1, 333),
(2, 2, 444);

insert into Poziomy values
(100),
(180),
(300),
(500);

insert into Misje (id_docelowej_lokacji, id_potwora_do_zabicia, id_nagrody, id_NPC, ilosc_do_zabicia, pieniadze, xp, opis) values
(1, null, null, 1, null, 200, 30, 'Dojdż tam gdzie trzeba.'),
(null, 1, null, 1, 5, 50, 10, 'Zabij krowy.');

commit;
