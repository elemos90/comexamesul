<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Models\Discipline;
use App\Models\ExamLocation;
use App\Models\ExamRoom;
use App\Services\ActivityLogger;
use App\Utils\Auth;
use App\Utils\Flash;
use App\Utils\Validator;

class MasterDataController extends Controller
{
    // ============================================
    // DISCIPLINAS
    // ============================================
    
    public function disciplines(): string
    {
        $disciplineModel = new Discipline();
        $disciplines = $disciplineModel->withJuryCount();
        
        return $this->view('master_data/disciplines', [
            'disciplines' => $disciplines,
            'user' => Auth::user()
        ]);
    }
    
    public function storeDiscipline(Request $request)
    {
        $data = $request->only(['code', 'name', 'description']);
        
        $validator = new Validator();
        $rules = [
            'code' => 'required|min:2|max:20',
            'name' => 'required|min:3|max:180',
        ];
        
        if (!$validator->validate($data, $rules)) {
            Flash::add('error', 'Verifique os dados da disciplina.');
            $_SESSION['errors'] = $validator->errors();
            redirect('/master-data/disciplines');
        }
        
        $disciplineModel = new Discipline();
        
        // Verificar se código já existe
        if ($disciplineModel->codeExists($data['code'])) {
            Flash::add('error', 'Código da disciplina já existe.');
            redirect('/master-data/disciplines');
        }
        
        $id = $disciplineModel->create([
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'active' => 1,
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        ActivityLogger::log('disciplines', $id, 'create');
        Flash::add('success', 'Disciplina cadastrada com sucesso.');
        redirect('/master-data/disciplines');
    }
    
    public function updateDiscipline(Request $request)
    {
        $id = (int) $request->param('id');
        $data = $request->only(['code', 'name', 'description']);
        
        $disciplineModel = new Discipline();
        $discipline = $disciplineModel->find($id);
        
        if (!$discipline) {
            Flash::add('error', 'Disciplina não encontrada.');
            redirect('/master-data/disciplines');
        }
        
        $validator = new Validator();
        $rules = [
            'code' => 'required|min:2|max:20',
            'name' => 'required|min:3|max:180',
        ];
        
        if (!$validator->validate($data, $rules)) {
            Flash::add('error', 'Verifique os dados da disciplina.');
            redirect('/master-data/disciplines');
        }
        
        // Verificar se código já existe (excluindo o atual)
        if ($disciplineModel->codeExists($data['code'], $id)) {
            Flash::add('error', 'Código da disciplina já existe.');
            redirect('/master-data/disciplines');
        }
        
        $disciplineModel->update($id, [
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'updated_at' => now(),
        ]);
        
        ActivityLogger::log('disciplines', $id, 'update');
        Flash::add('success', 'Disciplina atualizada.');
        redirect('/master-data/disciplines');
    }
    
    public function toggleDiscipline(Request $request)
    {
        $id = (int) $request->param('id');
        $disciplineModel = new Discipline();
        
        if ($disciplineModel->toggleActive($id)) {
            ActivityLogger::log('disciplines', $id, 'toggle_status');
            Response::json(['success' => true, 'message' => 'Status alterado.']);
        }
        
        Response::json(['success' => false, 'message' => 'Erro ao alterar status.'], 400);
    }
    
    public function deleteDiscipline(Request $request)
    {
        $id = (int) $request->param('id');
        $disciplineModel = new Discipline();
        
        // Verificar se há júris vinculados
        $juryCount = $disciplineModel->statement(
            "SELECT COUNT(*) as count FROM juries WHERE discipline_id = :id",
            ['id' => $id]
        );
        
        if (($juryCount[0]['count'] ?? 0) > 0) {
            Flash::add('error', 'Não é possível eliminar: existem júris vinculados a esta disciplina.');
            redirect('/master-data/disciplines');
        }
        
        $disciplineModel->delete($id);
        ActivityLogger::log('disciplines', $id, 'delete');
        Flash::add('success', 'Disciplina eliminada.');
        redirect('/master-data/disciplines');
    }
    
    // ============================================
    // LOCAIS
    // ============================================
    
    public function locations(): string
    {
        $locationModel = new ExamLocation();
        $locations = $locationModel->withDetails();
        
        return $this->view('master_data/locations', [
            'locations' => $locations,
            'user' => Auth::user()
        ]);
    }
    
    public function storeLocation(Request $request)
    {
        $data = $request->only(['code', 'name', 'address', 'city', 'capacity', 'description']);
        
        $validator = new Validator();
        $rules = [
            'code' => 'required|min:2|max:20',
            'name' => 'required|min:3|max:150',
        ];
        
        if (!$validator->validate($data, $rules)) {
            Flash::add('error', 'Verifique os dados do local.');
            $_SESSION['errors'] = $validator->errors();
            redirect('/master-data/locations');
        }
        
        $locationModel = new ExamLocation();
        
        if ($locationModel->codeExists($data['code'])) {
            Flash::add('error', 'Código do local já existe.');
            redirect('/master-data/locations');
        }
        
        $id = $locationModel->create([
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'capacity' => !empty($data['capacity']) ? (int) $data['capacity'] : null,
            'description' => $data['description'] ?? null,
            'active' => 1,
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        ActivityLogger::log('exam_locations', $id, 'create');
        Flash::add('success', 'Local cadastrado com sucesso.');
        redirect('/master-data/locations');
    }
    
    public function updateLocation(Request $request)
    {
        $id = (int) $request->param('id');
        $data = $request->only(['code', 'name', 'address', 'city', 'capacity', 'description']);
        
        $locationModel = new ExamLocation();
        $location = $locationModel->find($id);
        
        if (!$location) {
            Flash::add('error', 'Local não encontrado.');
            redirect('/master-data/locations');
        }
        
        $validator = new Validator();
        $rules = [
            'code' => 'required|min:2|max:20',
            'name' => 'required|min:3|max:150',
        ];
        
        if (!$validator->validate($data, $rules)) {
            Flash::add('error', 'Verifique os dados do local.');
            redirect('/master-data/locations');
        }
        
        if ($locationModel->codeExists($data['code'], $id)) {
            Flash::add('error', 'Código do local já existe.');
            redirect('/master-data/locations');
        }
        
        $locationModel->update($id, [
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'capacity' => !empty($data['capacity']) ? (int) $data['capacity'] : null,
            'description' => $data['description'] ?? null,
            'updated_at' => now(),
        ]);
        
        ActivityLogger::log('exam_locations', $id, 'update');
        Flash::add('success', 'Local atualizado.');
        redirect('/master-data/locations');
    }
    
    public function toggleLocation(Request $request)
    {
        $id = (int) $request->param('id');
        $locationModel = new ExamLocation();
        
        if ($locationModel->toggleActive($id)) {
            ActivityLogger::log('exam_locations', $id, 'toggle_status');
            Response::json(['success' => true, 'message' => 'Status alterado.']);
        }
        
        Response::json(['success' => false, 'message' => 'Erro ao alterar status.'], 400);
    }
    
    public function deleteLocation(Request $request)
    {
        $id = (int) $request->param('id');
        $locationModel = new ExamLocation();
        
        // Verificar se há júris vinculados
        $juryCount = $locationModel->statement(
            "SELECT COUNT(*) as count FROM juries WHERE location_id = :id",
            ['id' => $id]
        );
        
        if (($juryCount[0]['count'] ?? 0) > 0) {
            Flash::add('error', 'Não é possível eliminar: existem júris vinculados a este local.');
            redirect('/master-data/locations');
        }
        
        $locationModel->delete($id);
        ActivityLogger::log('exam_locations', $id, 'delete');
        Flash::add('success', 'Local eliminado.');
        redirect('/master-data/locations');
    }
    
    // ============================================
    // SALAS
    // ============================================
    
    public function rooms(Request $request): string
    {
        $locationId = (int) ($request->query('location') ?? 0);
        
        $locationModel = new ExamLocation();
        $roomModel = new ExamRoom();
        
        $locations = $locationModel->getActive();
        
        if ($locationId > 0) {
            $selectedLocation = $locationModel->find($locationId);
            $rooms = $roomModel->withJuryCount($locationId);
        } else {
            $selectedLocation = null;
            $rooms = [];
        }
        
        return $this->view('master_data/rooms', [
            'locations' => $locations,
            'selectedLocation' => $selectedLocation,
            'rooms' => $rooms,
            'locationId' => $locationId,
            'user' => Auth::user()
        ]);
    }
    
    public function storeRoom(Request $request)
    {
        $data = $request->only(['location_id', 'code', 'name', 'capacity', 'floor', 'building', 'notes']);
        
        $validator = new Validator();
        $rules = [
            'location_id' => 'required|numeric',
            'code' => 'required|min:1|max:20',
            'name' => 'required|min:2|max:60',
            'capacity' => 'required|numeric',
        ];
        
        if (!$validator->validate($data, $rules)) {
            Flash::add('error', 'Verifique os dados da sala.');
            $_SESSION['errors'] = $validator->errors();
            redirect('/master-data/rooms?location=' . ($data['location_id'] ?? ''));
        }
        
        $roomModel = new ExamRoom();
        $locationId = (int) $data['location_id'];
        
        if ($roomModel->codeExistsInLocation($locationId, $data['code'])) {
            Flash::add('error', 'Código da sala já existe neste local.');
            redirect('/master-data/rooms?location=' . $locationId);
        }
        
        $id = $roomModel->create([
            'location_id' => $locationId,
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'capacity' => (int) $data['capacity'],
            'floor' => $data['floor'] ?? null,
            'building' => $data['building'] ?? null,
            'notes' => $data['notes'] ?? null,
            'active' => 1,
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        ActivityLogger::log('exam_rooms', $id, 'create');
        Flash::add('success', 'Sala cadastrada com sucesso.');
        redirect('/master-data/rooms?location=' . $locationId);
    }
    
    public function updateRoom(Request $request)
    {
        $id = (int) $request->param('id');
        $data = $request->only(['location_id', 'code', 'name', 'capacity', 'floor', 'building', 'notes']);
        
        $roomModel = new ExamRoom();
        $room = $roomModel->find($id);
        
        if (!$room) {
            Flash::add('error', 'Sala não encontrada.');
            redirect('/master-data/rooms');
        }
        
        $validator = new Validator();
        $rules = [
            'location_id' => 'required|numeric',
            'code' => 'required|min:1|max:20',
            'name' => 'required|min:2|max:60',
            'capacity' => 'required|numeric',
        ];
        
        if (!$validator->validate($data, $rules)) {
            Flash::add('error', 'Verifique os dados da sala.');
            redirect('/master-data/rooms?location=' . $room['location_id']);
        }
        
        $locationId = (int) $data['location_id'];
        
        if ($roomModel->codeExistsInLocation($locationId, $data['code'], $id)) {
            Flash::add('error', 'Código da sala já existe neste local.');
            redirect('/master-data/rooms?location=' . $locationId);
        }
        
        $roomModel->update($id, [
            'location_id' => $locationId,
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'capacity' => (int) $data['capacity'],
            'floor' => $data['floor'] ?? null,
            'building' => $data['building'] ?? null,
            'notes' => $data['notes'] ?? null,
            'updated_at' => now(),
        ]);
        
        // Atualizar automaticamente TODOS os dados da sala em todos os júris que a usam
        $updatedRoom = $roomModel->find($id);
        $locationModel = new \App\Models\ExamLocation();
        $location = $locationModel->find($updatedRoom['location_id']);
        
        // Criar texto descritivo da sala
        $roomText = $updatedRoom['name'] ?: $updatedRoom['code'];
        
        $locationParts = [];
        if (!empty($updatedRoom['building'])) {
            $locationParts[] = $updatedRoom['building'];
        }
        if (!empty($updatedRoom['floor'])) {
            $locationParts[] = $updatedRoom['floor'];
        }
        if (!empty($locationParts)) {
            $roomText .= ' (' . implode(' | ', $locationParts) . ')';
        }
        
        // Atualizar todos os júris que referenciam esta sala
        $juryModel = new \App\Models\Jury();
        $juriesWithRoom = $juryModel->statement(
            "SELECT id FROM juries WHERE room_id = :room_id",
            ['room_id' => $id]
        );
        
        foreach ($juriesWithRoom as $jury) {
            $juryModel->update($jury['id'], [
                'room' => $roomText,
                'room_id' => $updatedRoom['id'],
                'location_id' => $updatedRoom['location_id'],
                'location' => $location ? $location['name'] : null,
                'updated_at' => now()
            ]);
        }
        
        $updatedCount = count($juriesWithRoom);
        
        ActivityLogger::log('exam_rooms', $id, 'update', [
            'juries_updated' => $updatedCount
        ]);
        
        if ($updatedCount > 0) {
            Flash::add('success', "Sala atualizada. {$updatedCount} júri(s) sincronizado(s) automaticamente.");
        } else {
            Flash::add('success', 'Sala atualizada.');
        }
        
        redirect('/master-data/rooms?location=' . $locationId);
    }
    
    public function toggleRoom(Request $request)
    {
        $id = (int) $request->param('id');
        $roomModel = new ExamRoom();
        
        if ($roomModel->toggleActive($id)) {
            ActivityLogger::log('exam_rooms', $id, 'toggle_status');
            Response::json(['success' => true, 'message' => 'Status alterado.']);
        }
        
        Response::json(['success' => false, 'message' => 'Erro ao alterar status.'], 400);
    }
    
    public function deleteRoom(Request $request)
    {
        $id = (int) $request->param('id');
        $roomModel = new ExamRoom();
        
        $room = $roomModel->find($id);
        if (!$room) {
            Flash::add('error', 'Sala não encontrada.');
            redirect('/master-data/rooms');
        }
        
        // Verificar se há júris vinculados
        $juryCount = $roomModel->statement(
            "SELECT COUNT(*) as count FROM juries WHERE room_id = :id",
            ['id' => $id]
        );
        
        if (($juryCount[0]['count'] ?? 0) > 0) {
            Flash::add('error', 'Não é possível eliminar: existem júris vinculados a esta sala.');
            redirect('/master-data/rooms?location=' . $room['location_id']);
        }
        
        $locationId = $room['location_id'];
        $roomModel->delete($id);
        ActivityLogger::log('exam_rooms', $id, 'delete');
        Flash::add('success', 'Sala eliminada.');
        redirect('/master-data/rooms?location=' . $locationId);
    }
    
    // ============================================
    // API: Buscar salas por local
    // ============================================
    
    public function getRoomsByLocation(Request $request)
    {
        $locationId = (int) $request->param('id');
        
        if ($locationId <= 0) {
            Response::json(['success' => false, 'message' => 'Local inválido.'], 400);
        }
        
        $roomModel = new ExamRoom();
        $rooms = $roomModel->getByLocation($locationId, true);
        
        Response::json([
            'success' => true,
            'rooms' => $rooms
        ]);
    }
}
