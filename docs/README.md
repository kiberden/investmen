# Документация Investman (Obsidian)

Эта папка подготовлена для использования в Obsidian как knowledge base проекта.

## Разделы

- [[Основное/overview|Общее]]
- [[Архитектура/overview|Архитектура]]
- [[База данных/overview|База]]
- [Architecture (markdown)](architecture.md)
- [Testing (markdown)](testing.md)

Актуальная архитектурная схема для TBank фиксирует multi-account модель (`Broker hasMany Account`) и write-path синхронизацию счетов.

## Что удалено как неактуальное

Удалены документы, которые отражали старую границу ответственности и требовали полной переработки:

- старый общий backend-bank-gateway markdown;
- старая C4-схема backend-bank-gateway;
- старая диаграмма классов по realtime портфелю.

## Источник внешней спецификации

- T-Bank Invest API: https://developer.tbank.ru/invest/services/operations/methods#/#portfoliostream
