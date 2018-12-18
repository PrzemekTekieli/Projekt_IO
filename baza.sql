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
    id_docelowej_lokacji    integer references Lokacje,
    id_potwora_do_zabicia   integer references Potwory,
    iloscDoZabicia          integer,
    pieniadze               integer,
    id_nagrody              integer references Przedmiot
);

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
(40, 35, 100),
(42, 5, 12),
(3, 3, 3);

insert into Kategoria (nazwa, opis) values
('Naramiennik', 'Coś co zakładasz dla ochrony kończyn górnych.'),
('Buty', 'Zakładasz na stopy.'),
('Rękawice', 'Zakładasz na dłonie.'),
('Spodnie', 'Zakładasz na nogi.'),
('Hełm', 'Zakładasz na łeb i masz zakuty.');

insert into Postacie (id_gracza, id_lokacji, id_statystyki, nazwa) values
(1, 1, 1, 'Alduin'),
(1, 1, 2, 'Bezimienny'),
(2, 4, 3, 'Geralt'),
(3, 2, 4, 'Lars'),
(2, 3, 5, 'Eldric');

insert into Ekwipunek (id_ekwipunku, id_postaci, pieniadze) values
(1, 2500),
(2, 100),
(3, 42),
(4, 5),
(5, 8219);

insert into NPC values
(1, 1, true, 'John'),
(2, 2, false, 'David'),     
(3, 3, false, 'Mark'),
(4, 4, false, 'Zenon'),
(5, 4, false, 'Nastain');

insert into Potwory (id_statystyki, nazwa, pieniadze) values
(1, 'Krowa', 3),
(2, 'Konik', 4),
(3, 'Smok', 456),
(4, 'Goryl', 523),
(5, 'Kuc', 23);

insert into Przedmiot (id_statystyki, id_kategorii, nazwa, cena, opis) values
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

commit;
