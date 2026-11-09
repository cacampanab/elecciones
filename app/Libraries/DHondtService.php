<?php

namespace App\Libraries;

class DHondtService
{
    /**
     * @param array<int, array{
     *     id:int|string,
     *     name:string,
     *     list_number:int|string,
     *     votes:int,
     *     previous_votes?:int,
     *     candidates?:array<int, array{id:int|string, first_name:string, last_name:string, position?:int|null}>
     * }> $lists
     */
    public function allocate(array $lists, int $seats): array
    {
        $seats = max(0, $seats);
        $quotients = [];

        foreach ($lists as $list) {
            $votes = max(0, (int)($list['votes'] ?? 0));
            if ($votes === 0) {
                continue;
            }

            for ($divisor = 1; $divisor <= $seats; $divisor++) {
                $quotients[] = [
                    'list_id' => $list['id'],
                    'list_name' => $list['name'],
                    'list_number' => (int)($list['list_number'] ?? 0),
                    'votes' => $votes,
                    'previous_votes' => max(0, (int)($list['previous_votes'] ?? 0)),
                    'divisor' => $divisor,
                    'quotient' => $votes / $divisor,
                ];
            }
        }

        usort($quotients, static function (array $a, array $b): int {
            $diff = $b['quotient'] <=> $a['quotient'];
            if (abs($b['quotient'] - $a['quotient']) > 0.0000001) {
                return $b['quotient'] <=> $a['quotient'];
            }
            if ($a['previous_votes'] !== $b['previous_votes']) {
                return $b['previous_votes'] <=> $a['previous_votes'];
            }
            if ($a['votes'] !== $b['votes']) {
                return $b['votes'] <=> $a['votes'];
            }
            if ($a['list_number'] !== $b['list_number']) {
                return $a['list_number'] <=> $b['list_number'];
            }

            return (int)$a['list_id'] <=> (int)$b['list_id'];
        });

        $winningQuotients = array_slice($quotients, 0, $seats);
        $seatCounts = [];
        foreach ($winningQuotients as $row) {
            $listId = (int)$row['list_id'];
            $seatCounts[$listId] = ($seatCounts[$listId] ?? 0) + 1;
        }

        $result = [];
        $unfilledSeats = 0;
        $warnings = [];

        foreach ($lists as $list) {
            $listId = (int)$list['id'];
            $seatsWon = $seatCounts[$listId] ?? 0;
            $candidates = $list['candidates'] ?? [];

            usort($candidates, static function (array $a, array $b): int {
                $positionA = (int)($a['position'] ?? 0);
                $positionB = (int)($b['position'] ?? 0);
                if ($positionA !== $positionB) {
                    return $positionA <=> $positionB;
                }

                return (int)$a['id'] <=> (int)$b['id'];
            });

            $elected = array_slice($candidates, 0, $seatsWon);
            $missing = max(0, $seatsWon - count($elected));
            $unfilledSeats += $missing;

            if ($missing > 0) {
                $warnings[] = $list['name'] . ' ganó ' . $seatsWon . ' escaño(s), pero solo tiene ' . count($candidates) . ' candidato(s). Faltan ' . $missing . '.';
            }

            $result[] = [
                'id' => $listId,
                'name' => $list['name'],
                'list_number' => (int)($list['list_number'] ?? 0),
                'votes' => max(0, (int)($list['votes'] ?? 0)),
                'previous_votes' => max(0, (int)($list['previous_votes'] ?? 0)),
                'seats' => $seatsWon,
                'candidates' => $candidates,
                'elected' => $elected,
                'missing_candidates' => $missing,
            ];
        }

        usort($result, static function (array $a, array $b): int {
            if ($a['seats'] !== $b['seats']) {
                return $b['seats'] <=> $a['seats'];
            }
            if ($a['votes'] !== $b['votes']) {
                return $b['votes'] <=> $a['votes'];
            }

            return $a['list_number'] <=> $b['list_number'];
        });

        if ($unfilledSeats > 0) {
            $warnings[] = 'Hay ' . $unfilledSeats . ' escaño(s) sin candidato suficiente. Defina la regla del CNE para completarlos.';
        }

        return [
            'seats' => $seats,
            'rows' => $result,
            'quotients' => $winningQuotients,
            'unfilled_seats' => $unfilledSeats,
            'warnings' => $warnings,
        ];
    }
}
